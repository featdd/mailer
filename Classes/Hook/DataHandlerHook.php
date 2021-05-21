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

use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * @package Featdd\Mailer\Hook
 */
class DataHandlerHook
{
    /**
     * @param array $incomingFieldArray
     * @param string $table
     * @param string|int $id
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, string $table, $id, DataHandler $dataHandler): void
    {
        if (
            'tt_content' === $table &&
            'mailer_form' === $incomingFieldArray['CType']
        ) {
            // TODO: fetch dynamic field values
            $someTestFieldValue = $incomingFieldArray['sometestfield'];

            unset($incomingFieldArray['sometestfield']);
        }
    }

    /**
     * @param string $status
     * @param string $table
     * @param $id
     * @param array $fieldArray
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processDatamap_postProcessFieldArray(string $status, string $table, $id, array &$fieldArray, DataHandler $dataHandler): void
    {
        if (
            'tt_content' === $table &&
            'mailer_form' === $fieldArray['CType']
        ) {
            // TODO: dynamicely unset field again due to possible default set
            unset($fieldArray['sometestfield']);
        }
    }
}
