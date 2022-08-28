<?php
declare(strict_types=1);

namespace Featdd\Mailer\Utility;

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

use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @package Featdd\Mailer\Utility
 */
class SettingsUtility
{
    public const EXTENSION_KEY = 'mailer';
    public const EXTENSION_NAME = 'Mailer';
    public const EXTENSION_PLUGIN_KEY = 'tx_' . self::EXTENSION_KEY;

    /**
     * @var array|null
     */
    protected static ?array $extensionTypoScript = null;

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

        if (($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController) {
            $typoScriptConfiguration = $GLOBALS['TSFE']->tmpl->setup ?? [];
        } else {
            /** @var \TYPO3\CMS\Core\TypoScript\TemplateService $template */
            $template = GeneralUtility::makeInstance(TemplateService::class);
            $template->tt_track = false;
            $template->setProcessExtensionStatics(true);
            $template->runThroughTemplates([]);
            $template->generateConfig();

            $typoScriptConfiguration = $template->setup ?? [];
        }

        $typoScriptConfiguration = GeneralUtility::removeDotsFromTS($typoScriptConfiguration);

        if (
            true === array_key_exists('plugin', $typoScriptConfiguration) &&
            true === array_key_exists(SettingsUtility::EXTENSION_PLUGIN_KEY, $typoScriptConfiguration['plugin'])
        ) {
            $extensionTypoScript = $typoScriptConfiguration['plugin'][SettingsUtility::EXTENSION_PLUGIN_KEY] ?? [];
        } else {
            $extensionTypoScript = [];
        }

        self::$extensionTypoScript = $extensionTypoScript;

        return $extensionTypoScript;
    }
}
