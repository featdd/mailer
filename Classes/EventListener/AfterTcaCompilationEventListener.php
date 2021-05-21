<?php
declare(strict_types=1);

namespace Featdd\Mailer\EventListener;

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

use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;

/**
 * @package Featdd\Mailer\EventListener
 */
class AfterTcaCompilationEventListener
{
    /**
     * @param \TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent $afterTcaCompilationEvent
     */
    public function __invoke(AfterTcaCompilationEvent $afterTcaCompilationEvent): void
    {
        $tca = $afterTcaCompilationEvent->getTca();

        // TODO: Make all this stuff dynamik
        // TODO: How or should dynamik palettes be allowed?

        $tca['tt_content']['columns']['sometestfield'] = [
            'exclude' => true,
            'label' => 'Testfeld',
//            'displayCond' => 'FIELD:header:REQ:true',
            'config' => [
                'type' => 'input',
                'site' => 30,
            ],
        ];

        $formIdentifier = 'mailer/Example';

        if (false === array_key_exists('displayCond', $tca['tt_content']['columns']['sometestfield'])) {
            $tca['tt_content']['columns']['sometestfield']['displayCond'] = 'FIELD:mailer_form:=:' . $formIdentifier;
        } else {
            $tca['tt_content']['columns']['sometestfield']['displayCond'] = [
                'AND' => [
                    'FIELD:mailer_form:=:' . $formIdentifier,
                    $tca['tt_content']['columns']['sometestfield']['displayCond'],
                ],
            ];
        }

        $newFields = ['sometestfield'];

        $tca['tt_content']['types']['mailer_form']['showitem'] = substr_replace(
            $tca['tt_content']['types']['mailer_form']['showitem'],
            implode(',', $newFields) . ',',
            stripos($tca['tt_content']['types']['mailer_form']['showitem'], '--palette--;;mailer_general,') + 28,
            0
        );

        $afterTcaCompilationEvent->setTca($tca);
    }
}
