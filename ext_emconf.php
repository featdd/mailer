<?php

$EM_CONF[\Featdd\Mailer\Utility\SettingsUtility::EXTENSION_KEY] = [
    'title' => 'Mailer',
    'description' => 'Mail form extension for simply or most advanced form integration',
    'category' => 'plugin',
    'author' => 'Daniel Dorndorf',
    'author_email' => 'mailer@featdd.de',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.4.0-11.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
