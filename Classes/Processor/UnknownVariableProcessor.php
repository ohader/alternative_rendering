<?php
namespace OliverHader\AlternativeRendering\Processor;

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

use OliverHader\AlternativeRendering\View\AbstractView;
use OliverHader\AlternativeRendering\RenderingContext;

/**
 * UnknownVariableProcessor
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class UnknownVariableProcessor implements \OliverHader\AlternativeRendering\ProcessorInterface {

	public function process(RenderingContext $renderingContext) {
		if (!$this->getSubstituteUnknownVariables($renderingContext)) {
			return;
		}

		$pattern = preg_quote(AbstractView::INDICATOR_Start, '!') . AbstractView::INDICATOR_InnerPattern . preg_quote(AbstractView::INDICATOR_End, '!');
		if (preg_match_all('!' . $pattern . '!', $renderingContext->getContent(), $matches)) {
			foreach ($matches['0'] as $variablePartial) {
				if (!$renderingContext->hasReplacement($variablePartial)) {
					$renderingContext->addReplacement($variablePartial, '');
				}
			}
		}
	}

	/**
	 * @param RenderingContext $renderingContext
	 * @return bool
	 */
	protected function getSubstituteUnknownVariables(RenderingContext $renderingContext) {
		return (bool)$renderingContext->getSetting(RenderingContext::SETTING_SubstituteUnknown);
	}

}