<?php
namespace OliverHader\AlternativeRendering\View;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * AbstractView
 *
 * Template Variables:
 * + #{<propertyPath>}# for all types
 *   Example: #{someVariable.property.subProperty}#
 * + #{<propertyPath>(<format>)}# for \DateTime types
 *   Example: #{someVariable.creationDate(Y-m-d)}#
 * + #{iterator:<propertyPath>(<variableName>)}# #{iterator.<variableName[.propertyPath]>}# #{/iterator:<propertyPath>}#
 *   Example: #{iterator:someVariable.property(someName)}# #{iterator.someName}# #{/iterator:someVariable.property}#
 *
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
abstract class AbstractView {

	const INDICATOR_Start = '#{';
	const INDICATOR_InnerPattern = '(?P<inner>[^#]+)';
	const INDICATOR_End = '}#';
	const INDICATOR_IteratorStartPattern = 'iterator:(?P<path>[^(}#]+)\((?P<name>[^)}#]+)\)';
	const INDICATOR_IteratorInnerPattern = '(?P<inner>.+?)';
	const INDICATOR_IteratorEndPattern = '/iterator:\1';
	const INDICATOR_VariableFormatPatter = '\((?P<format>[^)]+)\)$';

	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @var array
	 */
	protected $variables = array();

	/**
	 * @var bool
	 */
	protected $substituteUnknownVariables = TRUE;

	/**
	 * @return NULL|string
	 */
	abstract public function render();

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param string $content
	 * @return AbstractView
	 */
	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	public function getSubstituteUnknownVariables() {
		return $this->substituteUnknownVariables;
	}

	/**
	 * @param bool $substituteUnknownVariables
	 */
	public function setSubstituteUnknownVariables($substituteUnknownVariables) {
		$this->substituteUnknownVariables = (bool)$substituteUnknownVariables;
	}

	/**
	 * @param string $key
	 * @param array|object $value
	 * @return AbstractView
	 */
	public function assign($key, $value) {
		$key = strtolower($key);
		$this->variables[$key] = $value;
		return $this;
	}

	/**
	 * @param NULL|string $content
	 * @return NULL|string
	 */
	protected function substitute($content = NULL) {
		if ($content === NULL) {
			$content = $this->getContent();
		}

		if (!self::isSubstitutionRequired($content)) {
			return $content;
		}

		$search = array();
		$replace = array();

		$this->substituteIterator($content, $search, $replace);
		$this->substituteVariables($content, $search, $replace);
		$content = str_replace($search, $replace, $content);

		return $content;
	}

	/**
	 * @param string $content
	 * @param array $search
	 * @param array $replace
	 */
	protected function substituteIterator($content, array &$search, array &$replace) {
		$pattern = preg_quote(self::INDICATOR_Start, '!')
			. self::INDICATOR_IteratorStartPattern . preg_quote(self::INDICATOR_End, '!')
				. self::INDICATOR_IteratorInnerPattern
			. preg_quote(self::INDICATOR_Start, '!')
				. self::INDICATOR_IteratorEndPattern . preg_quote(self::INDICATOR_End, '!');

		if (preg_match_all('!' . $pattern . '!mis', $content, $matches)) {
			$variables = $this->variables;
			foreach ($matches[0] as $index => $iteratorPartial) {
				$iteratorContent = '';
				$iterator = $this->resolveVariable($matches['path'][$index], FALSE);

				if (is_array($iterator) || $iterator instanceof \Traversable) {
					foreach ($iterator as $value) {
						$iteratorVariables = array_merge(
							(array)$this->variables['iterator'],
							array($matches['name'][$index] => $value)
						);

						$this->assign('iterator', $iteratorVariables);
						$iteratorContent .= $this->substitute($matches['inner'][$index]);
					}
				}

				$search[] = $iteratorPartial;
				$replace[] = $iteratorContent;
			}
			$this->variables = $variables;
		}
	}

	/**
	 * @param string $content
	 * @param array $search
	 * @param array $replace
	 */
	protected function substituteVariables($content, array &$search, array &$replace) {
		$pattern = preg_quote(self::INDICATOR_Start, '!') . self::INDICATOR_InnerPattern . preg_quote(self::INDICATOR_End, '!');
		if (preg_match_all('!' . $pattern . '!', $content, $matches)) {
			foreach ($matches['0'] as $index => $variablePartial) {
				$value = $this->resolveVariable($matches['inner'][$index]);

				if ($this->getSubstituteUnknownVariables() || $value !== NULL) {
					$search[] = $variablePartial;
					$replace[] = $value;
				}
			}
		}
	}

	/**
	 * @param string $path
	 * @param boolean $toString
	 * @return NULL|\DateTime|string
	 */
	protected function resolveVariable($path, $toString = TRUE) {
		$format = NULL;

		if (preg_match('!' . self::INDICATOR_VariableFormatPatter . '!', $path, $matches)) {
			$format = $matches['format'];
			$path = str_replace($matches[0], '', $path);
		}

		$value = ObjectAccess::getPropertyPath($this->variables, $path);

		if ($value instanceof \DateTime) {
			$format = $format ?: 'Y-m-d';
			$value = $value->format($format);
		}

		if ($toString) {
			$value = (string)$value;
		}

		return $value;
	}

	/**
	 * @param string $content
	 * @return bool
	 */
	static public function isSubstitutionRequired($content) {
		return (strpos($content, self::INDICATOR_Start) !== FALSE && strpos($content, self::INDICATOR_End) !== FALSE);
	}

}