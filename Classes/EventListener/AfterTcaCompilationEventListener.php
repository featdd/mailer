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

use Featdd\Mailer\Service\ConfigurationService;
use Featdd\Mailer\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;

/**
 * @package Featdd\Mailer\EventListener
 */
class AfterTcaCompilationEventListener
{
    /**
     * @var \Featdd\Mailer\Service\ConfigurationService
     */
    protected ConfigurationService $configurationService;

    /**
     * @param \Featdd\Mailer\Service\ConfigurationService $configurationService
     */
    public function __construct(ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * @param \TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent $afterTcaCompilationEvent
     */
    public function __invoke(AfterTcaCompilationEvent $afterTcaCompilationEvent): void
    {
        $tca = $afterTcaCompilationEvent->getTca();

        foreach ($this->configurationService->loadAllConfigurations() as $formConfiguration) {
            foreach ($formConfiguration->getFinisher() as $finisher) {
                if (
                    null !== $finisher::tcaPaletteKey() &&
                    null !== $finisher::tcaPaletteLabel() &&
                    null !== $finisher::tcaPaletteShowItem() &&
                    null !== $finisher::tcaColumns()
                ) {
                    $tca['tt_content']['palettes'][$finisher::tcaPaletteKey()] = [
                        'label' => $finisher::tcaPaletteLabel(),
                        'showitem' => implode(',', $finisher::tcaPaletteShowItem()),
                    ];

                    $inserPosistion = false === stripos($tca['tt_content']['types']['mailer_form']['showitem'], '|||')
                        ? stripos($tca['tt_content']['types']['mailer_form']['showitem'], '--palette--;;mailer_general,') + 28
                        : stripos($tca['tt_content']['types']['mailer_form']['showitem'], '|||');

                    $tca['tt_content']['types']['mailer_form']['showitem'] = str_replace('|||', '', $tca['tt_content']['types']['mailer_form']['showitem']);

                    $tca['tt_content']['types']['mailer_form']['showitem'] = substr_replace(
                        $tca['tt_content']['types']['mailer_form']['showitem'],
                        '--palette--;;' . $finisher::tcaPaletteKey() . ',|||',
                        $inserPosistion,
                        0
                    );

                    foreach ($finisher::tcaColumns() as $formTcaColumn => $formTcaColumnData) {
                        if (false === array_key_exists('displayCond', $formTcaColumnData)) {
                            $formTcaColumnData['displayCond'] = 'FIELD:mailer_form:=:' . $formConfiguration->getIdentifier();
                        } else {
                            $formTcaColumnData['displayCond'] = [
                                'AND' => [
                                    'FIELD:mailer_form:=:' . $formConfiguration->getIdentifier(),
                                    $formTcaColumnData['displayCond'],
                                ],
                            ];
                        }

                        ConfigurationUtility::processTcaConfigurationFinisherOptions($formTcaColumnData, $finisher);

                        $tca['tt_content']['columns'][$formTcaColumn] = $formTcaColumnData;
                    }
                }
            }

            $tca['tt_content']['types']['mailer_form']['showitem'] = str_replace('|||', '', $tca['tt_content']['types']['mailer_form']['showitem']);
        }

        $afterTcaCompilationEvent->setTca($tca);
    }
}
