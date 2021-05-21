<?php
declare(strict_types=1);

namespace Featdd\Mailer\Utility;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class TranslationUtility
{
    public const REGEX_TRANSLATION_FILE_KEY = '/(LLL:[\w\/\:\.\-]+)/';

    /**
     * @param string $value
     * @return string
     */
    public static function translateLLL(string $value): string
    {
        if (0 < preg_match(self::REGEX_TRANSLATION_FILE_KEY, $value, $matches)) {
            $translation = ($GLOBALS['LANG'] ?? GeneralUtility::makeInstance(LanguageService::class))->sL($matches[0]);

            if (false === empty($translation)) {
                $value = preg_replace(self::REGEX_TRANSLATION_FILE_KEY, $translation, $value);
            }
        }

        return $value;
    }

    /**
     * @return string
     */
    public static function currentLanguageIsoCode(): string
    {
        return ($GLOBALS['LANG'] ?? GeneralUtility::makeInstance(LanguageService::class))->lang;
    }
}
