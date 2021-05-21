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
        if ('FE' === TYPO3_MODE) {
            $settings = SettingsUtility::settings();

            if (true === (bool) ($settings['addJavaScriptConfiguration'] ?? false)) {
                $configuration = $settings['javaScriptConfiguration'] ?? [];
                $javaScriptConfiguration = [];

                if (false === empty($configuration['errorClassName'])) {
                    $javaScriptConfiguration['errorClassName'] = $configuration['errorClassName'];
                }

                if (false === empty($configuration['errorClassParentTargetSelector'])) {
                    $javaScriptConfiguration['errorClassParentTargetSelector'] = $configuration['errorClassParentTargetSelector'];
                }

                if (false === empty($configuration['errorMessageTargetSelector'])) {
                    $javaScriptConfiguration['errorMessageTargetSelector'] = $configuration['errorMessageTargetSelector'];
                }

                if (false === empty($configuration['errorMessageTemplate'])) {
                    $javaScriptConfiguration['errorMessageTemplate'] = $configuration['errorMessageTemplate'];
                }

                $pageRenderer->addHeaderData(
                    '<script data-ignore="1">const mailerConfiguration = ' .
                    json_encode($javaScriptConfiguration) .
                    ';</script>'
                );
            }

            if (true === (bool) $settings['addJavaScript']) {
                $pageRenderer->addJsFooterLibrary(
                    'mailer',
                    PathUtility::getAbsoluteWebPath('typo3conf/ext/mailer/Resources/Public/JavaScript/Mailer.js')
                );
            }
        }
    }
}
