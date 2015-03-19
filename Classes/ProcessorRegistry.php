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
 * ProcessorRegistry
 * @author Oliver Hader <oliver.hader@typo3.org>
 */
class ProcessorRegistry implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var array|ProcessorInterface[]
	 */
	protected $regularHandlers = array();

	/**
	 * @var array|ProcessorInterface[]
	 */
	protected $finishHandlers = array();

	public function __construct() {
		$this->addRegular('iterator', 'OliverHader\\AlternativeRendering\\Processor\\IteratorProcessor');
		$this->addRegular('variable', 'OliverHader\\AlternativeRendering\\Processor\\VariableProcessor');
		$this->addFinish('unknownVariable', 'OliverHader\\AlternativeRendering\\Processor\\UnknownVariableProcessor');
	}

	/**
	 * @param string $name
	 * @param string $handlerClassName
	 */
	public function addRegular($name, $handlerClassName) {
		if (isset($this->regularHandlers[$name]) && !$this->regularHandlers[$name] instanceof $handlerClassName) {
			throw new \LogicException('Regular handler for "' . $name . '" is already registered', 1407317751);
		}

		$handler = GeneralUtility::makeInstance($handlerClassName);

		if (!$handler instanceof ProcessorInterface) {
			throw new \LogicException('Regular handler for "' . $name . '" does not implement ProcessorInterface', 1407317752);
		}

		$this->regularHandlers[$name] = $handler;
	}

	/**
	 * @param string $name
	 * @param string $handlerClassName
	 */
	public function addFinish($name, $handlerClassName) {
		if (isset($this->finishHandlers[$name]) && !$this->finishHandlers[$name] instanceof $handlerClassName) {
			throw new \LogicException('Finish handler for "' . $name . '" is already registered', 1407317751);
		}

		$handler = GeneralUtility::makeInstance($handlerClassName);

		if (!$handler instanceof ProcessorInterface) {
			throw new \LogicException('Finish handler for "' . $name . '" does not implement ProcessorInterface', 1407317752);
		}

		$this->finishHandlers[$name] = $handler;
	}

	/**
	 * @return array|ProcessorInterface[]
	 */
	public function getRegularHandlers() {
		return $this->regularHandlers;
	}

	/**
	 * @return array|ProcessorInterface[]
	 */
	public function getFinishHandlers() {
		return $this->finishHandlers;
	}

	/**
	 * @param RenderingContext $renderingContext
	 */
	public function processAll(RenderingContext $renderingContext) {
		foreach ($this->getRegularHandlers() as $handler) {
			$handler->process($renderingContext);
		}
		foreach ($this->getFinishHandlers() as $handler) {
			$handler->process($renderingContext);
		}
	}

	/**
	 * @return \OliverHader\AlternativeRendering\ProcessorRegistry
	 */
	static public function getInstance() {
		return GeneralUtility::makeInstance(
			'OliverHader\\AlternativeRendering\\ProcessorRegistry'
		);
	}

}