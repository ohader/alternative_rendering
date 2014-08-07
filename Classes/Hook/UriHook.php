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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * UriHook
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class UriHook extends AbstractConfigurationHook implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Ensures absolute URIs if configured in TypoScript.
	 *
	 * config.alternative_rendering {
	 *   absoluteUri = 1
	 *   secureUri = 1
	 * }
	 *
	 * @param array $parameters
	 * @param TypoScriptFrontendController $frontendController
	 */
	public function process(array $parameters, TypoScriptFrontendController $frontendController) {
		if (!$this->isAbsoluteUriEnabled($frontendController)) {
			return;
		}

		$secureUri = $this->isSecureUriEnabled($frontendController);
		$scheme = ($secureUri ? 'https' : 'http');
		$domain = $frontendController->getDomainDataForPid($frontendController->id);
		if (!empty($domain['domainName'])) {
			$domainName = $scheme . '://' . $domain['domainName'];
		} else {
			$domainName = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR');
			$currentScheme = strstr($domainName, '://', TRUE);
			if ($currentScheme !== $scheme) {
				$domainName = $scheme . substr($domainName, strlen($currentScheme));
			}
		}

		$domainName = rtrim($domainName, '/') . '/';
		$frontendController->config['config']['absRefPrefix'] = $domainName;
	}

	/**
	 * @param TypoScriptFrontendController $frontendController
	 * @return bool
	 */
	protected function isSecureUriEnabled(TypoScriptFrontendController $frontendController) {
		return $this->isConfigurationEnabled('secureUri', $frontendController);
	}

	/**
	 * @param TypoScriptFrontendController $frontendController
	 * @return bool
	 */
	protected function isAbsoluteUriEnabled(TypoScriptFrontendController $frontendController) {
		return $this->isConfigurationEnabled('absoluteUri', $frontendController);
	}

}