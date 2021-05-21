<?php
declare(strict_types=1);

namespace Featdd\Mailer\Utility;

use Featdd\Mailer\Utility\Exception\LanguageServiceException;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
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
     * @throws \Featdd\Mailer\Utility\Exception\LanguageServiceException
     */
    public static function translateLLL(string $value): string
    {
        if (0 < preg_match(self::REGEX_TRANSLATION_FILE_KEY, $value, $matches)) {
            $translation = self::languageService()->sL($matches[0]);

            if (false === empty($translation)) {
                $value = preg_replace(self::REGEX_TRANSLATION_FILE_KEY, $translation, $value);
            }
        }

        return $value;
    }

    /**
     * @return string
     * @throws \Featdd\Mailer\Utility\Exception\LanguageServiceException
     */
    public static function currentLanguageIsoCode(): string
    {
        return self::languageService()->lang;
    }

    /**
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     * @throws \Featdd\Mailer\Utility\Exception\LanguageServiceException
     */
    protected static function languageService(): LanguageService
    {
        $languageService = null;

        if (true === array_key_exists('LANG', $GLOBALS) && $GLOBALS['LANG'] instanceof LanguageService) {
            $languageService = $GLOBALS['LANG'];
        } else {
            /** @var \TYPO3\CMS\Core\Localization\LanguageServiceFactory $languageServiceFactory */
            $languageServiceFactory = GeneralUtility::makeInstance(LanguageServiceFactory::class);

            if (!($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequest) {
                $languageService = $languageServiceFactory->create('default');
            } else {
                $siteLanguage = $GLOBALS['TYPO3_REQUEST']->getAttribute('language');

                if ($siteLanguage instanceof SiteLanguage) {
                    $languageService = $languageServiceFactory->createFromSiteLanguage($siteLanguage);
                }
            }
        }

        if (!$languageService instanceof LanguageService) {
            throw new LanguageServiceException('Failed to instantiate language service');
        }

        return $languageService;
    }
}
