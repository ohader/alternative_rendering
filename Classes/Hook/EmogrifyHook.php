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

/**
 * EmogrifyHook
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class EmogrifyHook implements \TYPO3\CMS\Core\SingletonInterface {

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
	 * @param array $parameters
	 * @param TypoScriptFrontendController $frontendController
	 */
	public function processContent(array $parameters, TypoScriptFrontendController $frontendController) {
		if (!$this->isEmogrifyEnabled()) {
			return;
		}

		$frontendController->content = $this->getEmogrifyService()->process($frontendController->content);
	}

	/**
	 * @return bool
	 */
	protected function isEmogrifyEnabled() {
		$enabled = FALSE;

		$frontendController = $this->getFrontendController();
		if (isset($frontendController->pSetup['config.']['emogrify'])) {
			$enabled = (bool)$frontendController->pSetup['config.']['emogrify'];
		} elseif (isset($frontendController->config['config.']['emogrify'])) {
			$enabled = (bool)$frontendController->config['config.']['emogrify'];
		}

		return $enabled;
	}

	/**
	 * @return EmogrifyService
	 */
	protected function getEmogrifyService() {
		return GeneralUtility::makeInstance(
			'OliverHader\\AlternativeRendering\\Service\\EmogrifyService'
		);
	}

	/**
	 * @return TypoScriptFrontendController
	 */
	protected function getFrontendController() {
		return $GLOBALS['TSFE'];
	}

}