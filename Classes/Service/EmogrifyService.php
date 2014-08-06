<?php
namespace OliverHader\AlternativeRendering\Service;

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

/**
 * EmogrifyService
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class EmogrifyService implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \Pelago\Emogrifier
	 */
	protected $emogrifier;

	/**
	 * @var array
	 */
	protected $cssData = array();

	public function process($content) {
		if (!empty($this->cssData)) {
			$this->getEmogrifier()->setCss(
				implode(LF, $this->cssData)
			);
		}

		$this->getEmogrifier()->setHtml($content);
		$content = $this->getEmogrifier()->emogrify();

		return $content;
	}

	/**
	 * @param string $name
	 * @param string $data
	 */
	public function addCssData($name, $data) {
		$this->cssData[$name] = $data;
	}

	/**
	 * @return \Pelago\Emogrifier
	 */
	protected function getEmogrifier() {
		if (!isset($this->emogrifier)) {
			require_once dirname(dirname(__DIR__)) . '/Resources/Private/Php/Emogrifier/Classes/Emogrifier.php';
			$this->emogrifier = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Pelago\\Emogrifier');
		}
		return $this->emogrifier;
	}

}