<?php
declare(strict_types=1);

namespace Featdd\Mailer\Utility;

use Featdd\Mailer\Domain\Model\Form;
use Featdd\Mailer\Finisher\FinisherInterface;

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
class ConfigurationUtility
{
    /**
     * @param array $options
     * @param \Featdd\Mailer\Domain\Model\Form $form
     */
    public static function processFinisherOptionsFormVariables(array &$options, Form $form): void
    {
        self::replaceVariablesInArray(
            $options,
            function (array $match) use ($form) {
                if ($form->hasFieldValue($match[1])) {
                    return $form->getFieldValue($match[1])->getValue();
                }

                return '';
            }
        );
    }

    /**
     * @param array $configuration
     * @param \Featdd\Mailer\Finisher\FinisherInterface $finisher
     */
    public static function processTcaConfigurationFinisherOptions(array &$configuration, FinisherInterface $finisher): void
    {
        $finisherOptions = $finisher->getOptions();

        self::replaceVariablesInArray(
            $configuration,
            function (array $match) use ($finisherOptions) {
                if (true === array_key_exists($match[1], $finisherOptions)) {
                    return $finisherOptions[$match[1]];
                }

                return '';
            }
        );
    }

    /**
     * @param array $data
     * @param callable $replaceCallback
     */
    protected static function replaceVariablesInArray(array &$data, callable $replaceCallback)
    {
        array_walk_recursive($data, function (&$item) use ($replaceCallback) {
            if (true === is_string($item)) {
                $item = preg_replace_callback('/{(\w+)}/i', $replaceCallback, $item);
            }
        });
    }
}
