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

use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\Exception\InvalidFileNameException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Featdd\Mailer\Utility
 */
class PathUtility
{
    /**
     * @var array
     */
    protected static array $templatesCache = [];

    /**
     * @param string $template
     * @return string
     */
    public static function resolveAbsoluteTemplatePath(string $template): ?string
    {
        if (true === array_key_exists($template, self::$templatesCache)) {
            return self::$templatesCache[$template];
        }

        $templatePath = null;

        if (0 === strpos($template, 'EXT:')) {
            $templatePath = GeneralUtility::getFileAbsFileName($template);
        } elseif (0 === strpos($template, '/')) {
            $templatePath = GeneralUtility::getFileAbsFileName(ltrim($template, '/'));
        } else {
            foreach (SettingsUtility::view()['templateRootPaths'] ?? [] as $templateRootPath) {
                $absoluteTemplateRootPath = GeneralUtility::getFileAbsFileName($templateRootPath);

                if ('/' !== $absoluteTemplateRootPath[\strlen($absoluteTemplateRootPath) - 1]) {
                    $absoluteTemplateRootPath .= '/';
                }

                $realTemplatePath = $absoluteTemplateRootPath . $template . '.html';

                if (true === file_exists($realTemplatePath)) {
                    $templatePath = $realTemplatePath;
                }
            }
        }

        if (true === empty($templatePath)) {
            return null;
        }

        self::$templatesCache[$template] = $templatePath;

        return $templatePath;
    }

    /**
     * @param string $fileName
     * @return string
     */
    public static function sanitizeFileName(string $fileName): string
    {
        try {
            $fileName = GeneralUtility::makeInstance(LocalDriver::class)->sanitizeFileName($fileName);
        } catch (InvalidFileNameException $exception) {
            // ignore
        }

        return str_ireplace('@', '_at_', $fileName);
    }
}
