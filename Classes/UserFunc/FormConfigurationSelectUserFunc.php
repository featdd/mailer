<?php
declare(strict_types=1);

namespace Featdd\Mailer\UserFunc;

/***
 *
 * This file is part of the "Mailer" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2021 Daniel Dorndorf <dorndorf@featdd.de>
 *
 ***/

use Featdd\Mailer\Service\ConfigurationService;
use TYPO3\CMS\Backend\Form\FormDataProvider\AbstractItemProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Featdd\Mailer\UserFunc
 */
class FormConfigurationSelectUserFunc
{
    /**
     * @var \Featdd\Mailer\Service\ConfigurationService
     */
    protected ConfigurationService $configurationService;

    public function __construct()
    {
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
    }

    /**
     * @param array $configuration
     * @param \TYPO3\CMS\Backend\Form\FormDataProvider\AbstractItemProvider $itemProvider
     */
    public function formConfigurationsItems(array &$configuration, AbstractItemProvider $itemProvider): void
    {
        $formConfigurations = $this->configurationService->loadAllConfigurations();

        foreach ($formConfigurations as $configurationIdentifier => $formConfiguration) {
            $title = false === empty($formConfiguration->getWizardTitle())
                ? $formConfiguration->getWizardTitle()
                : $configurationIdentifier;

            $configuration['items'][] = [$title, $configurationIdentifier];
        }
    }
}
