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
		$this->setRenderingContext(RenderingContext::create());
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
		return $this->getRenderingContext()->getSetting(
			RenderingContext::SETTING_SubstituteUnknown
		);
	}

	/**
	 * @param bool $substituteUnknownVariables
	 */
	public function setSubstituteUnknownVariables($substituteUnknownVariables) {
		$this->getRenderingContext()->setSetting(
			RenderingContext::SETTING_SubstituteUnknown,
			(bool)$substituteUnknownVariables
		);
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

		return $renderingContext->render();
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
					$renderingContext->addReplacement($variablePartial, (string)$value);
				}
			}
		}
	}

	/**
	 * @param array $variables
	 * @param string $path
	 * @return NULL|\DateTime|string
	 */
	static public function resolveVariable(array $variables, $path) {
		$format = NULL;

		if (preg_match('!' . self::INDICATOR_VariableFormatPattern . '!', $path, $matches)) {
			$format = $matches['format'];
			$path = str_replace($matches[0], '', $path);
		}

		$value = self::resolveVariableValue($variables, $path);

		if ($value instanceof \DateTime) {
			$format = $format ?: 'Y-m-d';
			$value = $value->format($format);
		}

		return $value;
	}

	static public function resolveVariableValue(array $variables, $path) {
		$value = ObjectAccess::getPropertyPath($variables, $path);

		// Try again with properties like "what_ever"
		// converted to lower camel-case like "whatEver"
		if ($value === NULL) {
			$steps = explode('.', $path);
			foreach ($steps as &$step) {
				$step = GeneralUtility::underscoredToLowerCamelCase($step);
			}
			$path = implode('.', $steps);
			$value = ObjectAccess::getPropertyPath($variables, $path);
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