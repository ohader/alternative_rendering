<?php
defined('TYPO3_MODE') or die();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['alternative_rendering'] =
	'OliverHader\\AlternativeRendering\\Hook\EmogrifyHook->processResources';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']['alternative_rendering'] =
	'OliverHader\\AlternativeRendering\\Hook\EmogrifyHook->processContent';
