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

/**
 * PageView
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class PageView extends AbstractView {

	/**
	 * @var int
	 */
	protected $pageUid;

	/**
	 * @var int
	 */
	protected $pageType;

	/**
	 * @var int
	 */
	protected $languageUid;

	/**
	 * @param int $pageUid
	 * @param int $pageType
	 * @param int $languageUid
	 */
	public function __construct($pageUid, $pageType = 0, $languageUid = 0) {
		$this->setPageUid($pageUid);
		$this->setPageType($pageType);
		$this->setLanguageUid($languageUid);
	}

	/**
	 * @return NULL|string
	 */
	public function render() {
		$this->fetchContent();
		return $this->substitute();
	}

	/**
	 * @return int
	 */
	public function getPageUid() {
		return $this->pageUid;
	}

	/**
	 * @param int $pageUid
	 */
	public function setPageUid($pageUid) {
		$this->pageUid = (int)$pageUid;
	}

	/**
	 * @return int
	 */
	public function getPageType() {
		return $this->pageType;
	}

	/**
	 * @param int $pageType
	 */
	public function setPageType($pageType) {
		$this->pageType = (int)$pageType;
	}

	/**
	 * @return int
	 */
	public function getLanguageUid() {
		return $this->languageUid;
	}

	/**
	 * @param int $languageUid
	 */
	public function setLanguageUid($languageUid) {
		$this->languageUid = (int)$languageUid;
	}

	/**
	 * Fetches the page content.
	 */
	protected function fetchContent() {
		$uri = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'index.php?id=' . $this->pageUid
			. (!empty($this->pageType) ? '&type=' . $this->pageType : '')
			. (!empty($this->languageUid) ? '&L=' . $this->languageUid : '');
		$content = GeneralUtility::getUrl($uri);
		$this->setContent($content);
	}

}