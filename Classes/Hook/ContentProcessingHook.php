<?php
namespace OliverHader\AlternativeRendering\Hook;

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

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use OliverHader\AlternativeRendering\Service\EmogrifyService;
use OliverHader\AlternativeRendering\Bootstrap;

/**
 * ContentProcessingHook
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class ContentProcessingHook extends AbstractConfigurationHook implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Processes and collects resources (Stylesheets and JavaScripts).
	 *
	 * @param array $parameters
	 * @param PageRenderer $pageRenderer
	 */
	public function processResources(array $parameters, PageRenderer $pageRenderer) {
		if (!$this->isEmogrifyEnabled()) {
			return;
		}

		foreach ($parameters as $resourceType => $resourceCollection) {
			if (stripos($resourceType, 'js') === 0) {
				$parameters[$resourceType] = array();
			}
		}

		foreach ($parameters['cssFiles'] as $resourceName => $resourceData) {
			$resourcePath = GeneralUtility::getFileAbsFileName($resourceData['file']);
			if (file_exists($resourcePath)) {
				$this->getEmogrifyService()->addCssData($resourceName, file_get_contents($resourcePath));
			}
			unset($parameters['cssFiles'][$resourceName]);
		}
	}

	/**
	 * Processes the content by either
	 * + adding an accordant cache tag to avoid the fetching round-trip
	 * + emogrifying the collected resources
	 *
	 * @param array $parameters
	 * @param TypoScriptFrontendController $frontendController
	 */
	public function processContent(array $parameters, TypoScriptFrontendController $frontendController) {
		if ($this->hasHttpHeader()) {
			$tagName = Bootstrap::EXTENSION_Key
				. '_' . (int)$frontendController->id
				. '-' . (int)$frontendController->type
				. '-' . (int)$frontendController->sys_language_uid;
			$frontendController->addCacheTags(array($tagName));
		}

		if ($this->isEmogrifyEnabled()) {
			$frontendController->content = $this->getEmogrifyService()->process($frontendController->content);
		}
	}

	/**
	 * Determines whether the HTTP header is set
	 * (and thus, whether PageView invoked this request).
	 *
	 * @return bool
	 */
	protected function hasHttpHeader() {
		$httpHeaderName = str_replace('-', '_', strtoupper(Bootstrap::HTTP_Header));

		if (!empty($_SERVER[$httpHeaderName]) && $_SERVER[$httpHeaderName] === 'TRUE') {
			return TRUE;
		} elseif (!empty($_SERVER['HTTP_' . $httpHeaderName]) && $_SERVER['HTTP_' . $httpHeaderName] === 'TRUE') {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Determines whether emogrifying is enabled in configuration.
	 *
	 * @return bool
	 */
	protected function isEmogrifyEnabled() {
		return $this->isConfigurationEnabled('emogrify');
	}

	/**
	 * @return EmogrifyService
	 */
	protected function getEmogrifyService() {
		return GeneralUtility::makeInstance(
			'OliverHader\\AlternativeRendering\\Service\\EmogrifyService'
		);
	}

}