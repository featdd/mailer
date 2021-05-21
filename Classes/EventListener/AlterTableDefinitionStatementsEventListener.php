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
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

/**
 * @package Featdd\Mailer\EventListener
 */
class AlterTableDefinitionStatementsEventListener
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
     * @param \TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent $event
     */
    public function __invoke(AlterTableDefinitionStatementsEvent $event): void
    {
        $sqlColumns = $this->configurationFinisherSqlColumns();

        if (0 < count($sqlColumns)) {
            $columnDefinitions = PHP_EOL . implode(',' . PHP_EOL, $sqlColumns) . PHP_EOL;
            $sql = 'CREATE TABLE tt_content (' . $columnDefinitions . ');' . PHP_EOL;

            $event->addSqlData($sql);
        }
    }

    /**
     * @return array
     */
    protected function configurationFinisherSqlColumns(): array
    {
        $sqlColumns = [];

        foreach ($this->configurationService->loadAllConfigurations() as $formConfiguration) {
            foreach ($formConfiguration->getFinisher() as $finisher) {
                if (null !== $finisher::sqlColumns()) {
                    $sqlColumns += $finisher::sqlColumns();
                }
            }
        }

        return $sqlColumns;
    }
}
