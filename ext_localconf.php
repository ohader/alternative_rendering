<?php
defined('TYPO3_MODE') or die();

// Processing for mail templates (config.alternative_rendering.emogrifiy = 1)
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['alternative_rendering'] =
	'OliverHader\\AlternativeRendering\\Hook\\EmogrifyHook->processResources';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']['alternative_rendering'] =
	'OliverHader\\AlternativeRendering\\Hook\\EmogrifyHook->processContent';

// Processing for absolute URIs (config.alternative_rendering.absoluteUri = 1)
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']['alternative_rendering'] =
	'OliverHader\\AlternativeRendering\\Hook\\UriHook->process';
