<?php
declare(strict_types=1);

namespace Featdd\Mailer\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

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

/**
 * @package Featdd\Mailer\Utility
 */
class SettingsUtility
{
    public const EXTENSION_KEY = 'mailer';
    public const EXTENSION_NAME = 'Mailer';
    public const EXTENSION_PLUGIN_KEY = 'tx_mailer';

    /**
     * @var array
     */
    protected static $extensionTypoScript;

    /**
     * @return array
     */
    public static function settings(): array
    {
        return self::extensionTypoScript()['settings'] ?? [];
    }

    /**
     * @return array
     */
    public static function view(): array
    {
        return self::extensionTypoScript()['view'] ?? [];
    }

    /**
     * @return array
     */
    protected static function extensionTypoScript(): array
    {
        if (true === is_array(self::$extensionTypoScript)) {
            return self::$extensionTypoScript;
        }

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);

        try {
            $typoScriptConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            $typoScriptConfiguration = GeneralUtility::removeDotsFromTS($typoScriptConfiguration);
            $extensionTypoScript = $typoScriptConfiguration['plugin'][SettingsUtility::EXTENSION_PLUGIN_KEY] ?? [];

            self::$extensionTypoScript = $extensionTypoScript;
        } catch (InvalidConfigurationTypeException $exception) {
            $extensionTypoScript = [];
        }

        return $extensionTypoScript;
    }
}
