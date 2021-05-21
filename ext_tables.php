<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function ($extKey) {
        /** @var \Featdd\Mailer\Service\ConfigurationService $configurationService */
        $configurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Featdd\Mailer\Service\ConfigurationService::class);
        $configurationService->registerAllFormConfigurationBackendWizards();
    },
    \Featdd\Mailer\Utility\SettingsUtility::EXTENSION_KEY
);
