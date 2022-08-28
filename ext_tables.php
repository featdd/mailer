<?php

use Featdd\Mailer\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {

    /** @var \Featdd\Mailer\Service\ConfigurationService $configurationService */
    $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
    $configurationService->registerAllFormConfigurationBackendWizards();

});
