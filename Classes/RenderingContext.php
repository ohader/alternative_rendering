<?php
namespace OliverHader\AlternativeRendering;

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

/**
 * RenderingContext
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class RenderingContext {

	const SETTING_SubstituteUnknown = 'substituteUnknown';

	/**
	 * @var array
	 */
	protected $variables = array();

	/**
	 * @var array
	 */
	protected $replacements = array();

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var string
	 */
	protected $content = '';

	/**
	 * @return array
	 */
	public function getVariables() {
		return $this->variables;
	}

	/**
	 * @param array $variables
	 * @return RenderingContext
	 */
	public function setVariables(array $variables) {
		$this->variables = $variables;
		return $this;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getVariable($name) {
		return $this->variables[$name];
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return RenderingContext
	 */
	public function setVariable($name, $value) {
		$this->variables[$name] = $value;
		return $this;
	}

	/**
	 * @param string $name
	 * @return RenderingContext
	 */
	public function unsetVariable($name) {
		unset($this->variables[$name]);
		return $this;
	}

	/**
	 * @return array
	 */
	public function getReplacements() {
		return $this->replacements;
	}

	/**
	 * @param array $replacements
	 * @return RenderingContext
	 */
	public function setReplacements(array $replacements) {
		$this->replacements = $replacements;
		return $this;
	}

	/**
	 * @param string $search
	 * @param string $replace
	 * @return RenderingContext
	 */
	public function addReplacement($search, $replace) {
		$this->replacements[$search] = $replace;
		return $this;
	}

	/**
	 * @param string $search
	 * @return bool
	 */
	public function hasReplacement($search) {
		return isset($this->replacements[$search]);
	}

	/**
	 * @return array
	 */
	public function getSearch() {
		return array_keys($this->replacements);
	}

	/**
	 * @return array
	 */
	public function getReplace() {
		return array_values($this->replacements);
	}

	/**
	 * @return array
	 */
	public function getSettings() {
		return $this->settings;
	}

	/**
	 * @param array $settings
	 * @return RenderingContext
	 */
	public function setSettings(array $settings) {
		$this->settings = $settings;
		return $this;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getSetting($name) {
		return $this->settings[$name];
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function setSetting($name, $value) {
		$this->settings[$name] = $value;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param string $content
	 * @return RenderingContext
	 */
	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	/**
	 * @return RenderingContext
	 */
	public function duplicate() {
		$renderingContext = self::create();
		$sections = func_get_args();

		if (empty($sections)) {
			$sections = array(
				'settings',
				'variables',
				'content'
			);
		}

		foreach ($sections as $section) {
			$getMethodName = 'get' . ucfirst($section);
			$setMethodName = 'set' . ucfirst($section);
			if (method_exists($this, $getMethodName)
				&& method_exists($this, $setMethodName)) {
				$renderingContext->$setMethodName(
					$this->$getMethodName()
				);
			}
		}

		return $renderingContext;
	}

	/**
	 * @return string
	 */
	public function replace() {
		$this->setContent(
			str_replace(
				$this->getSearch(),
				$this->getReplace(),
				$this->getContent()
			)
		);
		return $this->getContent();
	}

	/**
	 * @return string
	 */
	public function render() {
		ProcessorRegistry::getInstance()->processAll($this);
		return $this->replace();
	}

	/**
	 * @return RenderingContext
	 */
	static public function create() {
		return GeneralUtility::makeInstance(
			'OliverHader\\AlternativeRendering\\RenderingContext'
		);
	}

}