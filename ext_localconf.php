<?php

use Featdd\Mailer\Controller\FormController;
use Featdd\Mailer\Hook\RenderPreProcessHook;
use Featdd\Mailer\Property\TypeConverter\FormTypeConverter;
use Featdd\Mailer\Property\TypeConverter\UploadFileTypeConverter;
use Featdd\Mailer\Service\ConfigurationService;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {

    ExtensionManagementUtility::addTypoScriptConstants(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mailer/Configuration/TypoScript/constants.typoscript">'
    );

    ExtensionManagementUtility::addTypoScriptSetup(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mailer/Configuration/TypoScript/setup.typoscript">'
    );

    ExtensionUtility::registerTypeConverter(FormTypeConverter::class);
    ExtensionUtility::registerTypeConverter(UploadFileTypeConverter::class);

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] = RenderPreProcessHook::class . '->preProcess';

    if (false === isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][ConfigurationService::CACHE_KEY_CONFIGURATIONS])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][ConfigurationService::CACHE_KEY_CONFIGURATIONS] = [
            'frontend' => PhpFrontend::class,
            'backend' => SimpleFileBackend::class,
            'options' => [
                'defaultLifetime' => 0,
            ],
        ];
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['mailer'][] = 'Featdd\\Mailer\\ViewHelpers';

    ExtensionUtility::configurePlugin(
        'mailer',
        'Form',
        [FormController::class => 'render, submit'],
        [FormController::class => 'submit'],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

    $iconRegistry->registerIcon(
        'mailer-plugin',
        FontawesomeIconProvider::class,
        ['name' => 'envelope']
    );

});
