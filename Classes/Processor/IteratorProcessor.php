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
 * IteratorProcessor
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class IteratorProcessor implements \OliverHader\AlternativeRendering\ProcessorInterface {

	const INDICATOR_IteratorStartPattern = 'iterator:(?P<path>[^(}#]+)\((?P<name>[^)}#]+)\)';
	const INDICATOR_IteratorInnerPattern = '(?P<inner>.+?)';
	const INDICATOR_IteratorEndPattern = '/iterator:\1';

	/**
	 * @param RenderingContext $renderingContext
	 */
	public function process(RenderingContext $renderingContext) {
		$pattern = preg_quote(AbstractView::INDICATOR_Start, '!')
			. self::INDICATOR_IteratorStartPattern . preg_quote(AbstractView::INDICATOR_End, '!')
				. self::INDICATOR_IteratorInnerPattern
			. preg_quote(AbstractView::INDICATOR_Start, '!')
				. self::INDICATOR_IteratorEndPattern . preg_quote(AbstractView::INDICATOR_End, '!');

		if (preg_match_all('!' . $pattern . '!mis', $renderingContext->getContent(), $matches)) {
			foreach ($matches[0] as $index => $iteratorPartial) {
				$iteratorContent = '';
				$iterator = AbstractView::resolveVariable($renderingContext->getVariables(), $matches['path'][$index]);

				if (is_array($iterator) || $iterator instanceof \Traversable) {
					foreach ($iterator as $value) {
						$iteratorVariables = array_merge(
							(array)$renderingContext->getVariables('iterator'),
							array($matches['name'][$index] => $value)
						);

						$iteratorRenderingContext = $renderingContext->duplicate('variables', 'settings')
							->setVariable('iterator', $iteratorVariables)
							->setContent($matches['inner'][$index]);
						$iteratorContent .= $iteratorRenderingContext->render();
					}
				}

				$renderingContext->addReplacement($iteratorPartial, $iteratorContent);
			}
		}
	}

}