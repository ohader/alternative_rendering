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

/**
 * SimpleView
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class SimpleView extends AbstractView {

	/**
	 * @param string $content
	 */
	public function __construct($content) {
		parent::__construct();
		$this->getRenderingContext()->setContent($content);
	}

	/**
	 * @return NULL|string
	 */
	public function render() {
		return $this->substitute();
	}

}