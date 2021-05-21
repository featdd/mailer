<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function (string $extKey) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptConstants(
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $extKey . '/Configuration/TypoScript/constants.typoscript">'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $extKey . '/Configuration/TypoScript/setup.typoscript">'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\Featdd\Mailer\Property\TypeConverter\FormTypeConverter::class);
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\Featdd\Mailer\Property\TypeConverter\UploadFileTypeConverter::class);

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] = \Featdd\Mailer\Hook\RenderPreProcessHook::class . '->preProcess';

        if (false === isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Featdd\Mailer\Service\ConfigurationService::CACHE_KEY_CONFIGURATIONS])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Featdd\Mailer\Service\ConfigurationService::CACHE_KEY_CONFIGURATIONS] = [
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend::class,
                'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
                'options' => [
                    'defaultLifetime' => 0,
                ],
            ];
        }

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['mailer'][] = 'Featdd\\Mailer\\ViewHelpers';

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            \Featdd\Mailer\Utility\SettingsUtility::EXTENSION_NAME,
            'Form',
            [\Featdd\Mailer\Controller\FormController::class => 'render, submit'],
            [\Featdd\Mailer\Controller\FormController::class => 'submit'],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

        $iconRegistry->registerIcon(
            'mailer-plugin',
            \TYPO3\CMS\Core\Imaging\IconProvider\FontawesomeIconProvider::class,
            ['name' => 'envelope']
        );
    },
    \Featdd\Mailer\Utility\SettingsUtility::EXTENSION_KEY
);
