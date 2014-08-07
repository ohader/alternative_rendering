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

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * AbstractConfigurationHook
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
abstract class AbstractConfigurationHook {

	/**
	 * @param string $propertyName
	 * @param TypoScriptFrontendController $frontendController
	 * @return bool
	 */
	protected function isConfigurationEnabled($propertyName, TypoScriptFrontendController $frontendController = NULL) {
		$enabled = FALSE;

		if ($frontendController === NULL) {
			$frontendController = $this->getFrontendController();
		}

		if (is_array($frontendController->pSetup) && isset($frontendController->pSetup['config']['alternative_rendering.'][$propertyName])) {
			$enabled = (bool)$frontendController->pSetup['config']['alternative_rendering.'][$propertyName];
		} elseif (is_array($frontendController->config) && isset($frontendController->config['config']['alternative_rendering.'][$propertyName])) {
			$enabled = (bool)$frontendController->config['config']['alternative_rendering.'][$propertyName];
		}

		return $enabled;
	}

	/**
	 * @param string $propertyName
	 * @param TypoScriptFrontendController $frontendController
	 * @return NULL|string
	 */
	protected function getScopeOfRegularConfiguration($propertyName, TypoScriptFrontendController $frontendController = NULL) {
		$scope = NULL;

		if ($frontendController === NULL) {
			$frontendController = $this->getFrontendController();
		}

		if (is_array($frontendController->pSetup) && isset($frontendController->pSetup['config'][$propertyName])) {
			$scope = 'page';
		} elseif (is_array($frontendController->config) && isset($frontendController->config['config'][$propertyName])) {
			$scope = 'config';
		}

		return $scope;
	}

	/**
	 * @return TypoScriptFrontendController
	 */
	protected function getFrontendController() {
		return $GLOBALS['TSFE'];
	}

}