<?php
defined('TYPO3_MODE') || die('Access denied.');

defined('ERROR_CODE_MKTOOLS') || define('ERROR_CODE_MKTOOLS', 160);

require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');

if (!function_exists('mktools_getConf')) {
	function mktools_getConf($key, $mode = false) {
		$extensionConfigurationByKey = tx_mklib_util_MiscTools::getExtensionValue($key, 'mktools');
		return (isset($extensionConfigurationByKey) && ($mode === false || TYPO3_MODE == $mode))
			? $extensionConfigurationByKey : false;
	}
}

if (mktools_getConf('contentReplaceActive', 'FE')) {
	// hook für Content Replace registrieren
	require_once(t3lib_extMgm::extPath('mktools', 'hook/class.tx_mktools_hook_ContentReplace.php'));
	// wenn der scriptmerger installiert ist, muss der replacer wie der scriptmerger aufgerufen werden.
	// der original replacer nutzt pageIndexing, der scripmerger die hooks contentPostProc-all und contentPostProc-output
	if (t3lib_extMgm::isLoaded('scriptmerger')) {
		//@TODO: eine möglichkeit finden, die hooks erst nach dem scriptmerger aufzurufen, ohne die extlist in der localconf anzupassen.
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][]
			= 'EXT:mktools/hook/class.tx_mktools_hook_ContentReplace.php:tx_mktools_hook_ContentReplace->contentPostProcOutput';
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][]
			= 'EXT:mktools/hook/class.tx_mktools_hook_ContentReplace.php:tx_mktools_hook_ContentReplace->contentPostProcAll';
	}
	// der normale weg
	else {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['pageIndexing'][]
			= 'tx_mktools_hook_ContentReplace';
	}
}

if (mktools_getConf('pageNotFoundHandling', 'FE')) {
	tx_rnbase::load('tx_mktools_util_PageNotFoundHandling');
	tx_mktools_util_PageNotFoundHandling::registerXclass();
}

if (mktools_getConf('realUrlXclass', 'FE')) {
	tx_rnbase::load('tx_mktools_util_RealUrl');
	tx_mktools_util_RealUrl::registerXclass();
}

require(t3lib_extMgm::extPath('mktools').'scheduler/ext_localconf.php');
