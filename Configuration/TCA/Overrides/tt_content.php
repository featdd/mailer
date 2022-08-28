<?php

use Featdd\Mailer\Preview\MailerFormPreviewRenderer;
use Featdd\Mailer\UserFunc\FormConfigurationSelectUserFunc;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3_MODE') or die();

call_user_func(function () {
    $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes']['mailer_form'] = 'mailer-plugin';

    ExtensionManagementUtility::addTCAcolumns(
        'tt_content',
        [
            'mailer_form' => [
                'exclude' => true,
                'label' => 'LLL:EXT:mailer/Resources/Private/Language/locallang.xlf:tca.form',
                'onChange' => 'reload',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'itemsProcFunc' => FormConfigurationSelectUserFunc::class . '->formConfigurationsItems',
                    'items' => [
                        [
                            'LLL:EXT:mailer/Resources/Private/Language/locallang.xlf:tca.form.select_a_form',
                            '',
                        ],
                    ],
                ],
            ],
        ]
    );

    $GLOBALS['TCA']['tt_content']['palettes']['mailer_general'] = [
        'label' => 'LLL:EXT:mailer/Resources/Private/Language/locallang.xlf:tca.tt_content.palette.mailer_general',
        'showitem' => 'mailer_form',
    ];

    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        '--div--;LLL:EXT:mailer/Resources/Private/Language/locallang.xlf:tca.tt_content.tabs.form_settings,--palette--;;mailer_general',
        'mailer_form',
        'after:header'
    );

    $GLOBALS['TCA']['tt_content']['types']['mailer_form']['showitem'] = '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;;general,
                --palette--;;header,
            --div--;LLL:EXT:mailer/Resources/Private/Language/locallang.xlf:tca.tt_content.tabs.form_settings,
                --palette--;;mailer_general,
            --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                --palette--;;frames,
                --palette--;;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;;access
        ';

    $GLOBALS['TCA']['tt_content']['types']['mailer_form']['previewRenderer'] = MailerFormPreviewRenderer::class;

    ExtensionUtility::registerPlugin(
        'mailer',
        'Form',
        'LLL:EXT:mailer/Resources/Private/Language/locallang.xlf:plugin.form.title',
        'mailer-plugin',
        'forms'
    );
});
