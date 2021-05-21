<?php
declare(strict_types=1);

namespace Featdd\Mailer\Hook;

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

use Featdd\Mailer\Utility\SettingsUtility;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * @package Featdd\Mailer\Hook
 */
class RenderPreProcessHook
{
    /**
     * @param array $params
     * @param \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer
     */
    public function preProcess(array $params, PageRenderer $pageRenderer): void
    {
        if (true === ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
            $settings = SettingsUtility::settings();

            if (true === (bool) ($settings['addJavaScript'] ?? false)) {
                $pageRenderer->addJsFooterLibrary(
                    'mailer',
                    PathUtility::getAbsoluteWebPath('typo3conf/ext/mailer/Resources/Public/JavaScript/Mailer.js')
                );
            }

            if (true === (bool) ($settings['addStylesheet'] ?? false)) {
                $pageRenderer->addCssFile(
                    PathUtility::getAbsoluteWebPath('typo3conf/ext/mailer/Resources/Public/Css/Mailer.css')
                );
            }
        }
    }
}
