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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use OliverHader\AlternativeRendering\RenderingContext;

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
	const INDICATOR_VariableFormatPattern = '\((?P<format>[^)]+)\)$';

	/**
	 * @var RenderingContext
	 */
	protected $renderingContext;

	/**
	 * @var bool
	 */
	protected $substituteUnknownVariables = TRUE;

	public function __construct() {
		$this->setRenderingContext(
			self::createRenderingContext()
		);
	}

	/**
	 * @return NULL|string
	 */
	abstract public function render();

	/**
	 * @return RenderingContext
	 */
	public function getRenderingContext() {
		return $this->renderingContext;
	}

	/**
	 * @param RenderingContext $renderingContext
	 * @return AbstractView
	 */
	public function setRenderingContext(RenderingContext $renderingContext) {
		$this->renderingContext = $renderingContext;
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
		$this->renderingContext->setVariable($key, $value);
		return $this;
	}

	/**
	 * @param RenderingContext $renderingContext
	 * @return NULL|string
	 */
	protected function substitute(RenderingContext $renderingContext = NULL) {
		if ($renderingContext === NULL) {
			$renderingContext = $this->renderingContext;
		}

		if (!self::isSubstitutionRequired($renderingContext->getContent())) {
			return $renderingContext->getContent();
		}

		$this->substituteIterator($renderingContext);
		$this->substituteVariables($renderingContext);

		return $renderingContext->replace();
	}

	/**
	 * @param RenderingContext $renderingContext
	 */
	protected function substituteIterator(RenderingContext $renderingContext) {
		$pattern = preg_quote(self::INDICATOR_Start, '!')
			. self::INDICATOR_IteratorStartPattern . preg_quote(self::INDICATOR_End, '!')
				. self::INDICATOR_IteratorInnerPattern
			. preg_quote(self::INDICATOR_Start, '!')
				. self::INDICATOR_IteratorEndPattern . preg_quote(self::INDICATOR_End, '!');

		if (preg_match_all('!' . $pattern . '!mis', $renderingContext->getContent(), $matches)) {
			foreach ($matches[0] as $index => $iteratorPartial) {
				$iteratorContent = '';
				$iterator = self::resolveVariable($renderingContext->getVariables(), $matches['path'][$index], FALSE);

				if (is_array($iterator) || $iterator instanceof \Traversable) {
					foreach ($iterator as $value) {
						$iteratorVariables = array_merge(
							(array)$renderingContext->getVariables('iterator'),
							array($matches['name'][$index] => $value)
						);

						$iteratorRenderingContext = self::createRenderingContext()
							->setVariables($renderingContext->getVariables())
							->setVariable('iterator', $iteratorVariables)
							->setContent($matches['inner'][$index]);
						$iteratorContent .= $this->substitute($iteratorRenderingContext);
					}
				}

				$renderingContext->addReplacement($iteratorPartial, $iteratorContent);
			}
		}
	}

	/**
	 * @param RenderingContext $renderingContext
	 */
	protected function substituteVariables(RenderingContext $renderingContext) {
		$pattern = preg_quote(self::INDICATOR_Start, '!') . self::INDICATOR_InnerPattern . preg_quote(self::INDICATOR_End, '!');
		if (preg_match_all('!' . $pattern . '!', $renderingContext->getContent(), $matches)) {
			foreach ($matches['0'] as $index => $variablePartial) {
				$value = self::resolveVariable($renderingContext->getVariables(), $matches['inner'][$index]);

				if ($this->getSubstituteUnknownVariables() || $value !== NULL) {
					$renderingContext->addReplacement($variablePartial, $value);
				}
			}
		}
	}

	/**
	 * @param array $variables
	 * @param string $path
	 * @param boolean $toString
	 * @return NULL|\DateTime|string
	 */
	static public function resolveVariable(array $variables, $path, $toString = TRUE) {
		$format = NULL;

		if (preg_match('!' . self::INDICATOR_VariableFormatPattern . '!', $path, $matches)) {
			$format = $matches['format'];
			$path = str_replace($matches[0], '', $path);
		}

		$value = ObjectAccess::getPropertyPath($variables, $path);

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

	/**
	 * @return RenderingContext
	 */
	static public function createRenderingContext() {
		return GeneralUtility::makeInstance(
			'OliverHader\\AlternativeRendering\\RenderingContext'
		);
	}

}