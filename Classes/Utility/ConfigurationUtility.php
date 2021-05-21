<?php
declare(strict_types=1);

namespace Featdd\Mailer\Utility;

use Featdd\Mailer\Domain\Model\Form;

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
        array_walk_recursive($options, function (string &$item, string &$key) use ($form) {
            $item = preg_replace_callback(
                '/{(\w+)}/i',
                function (array $match) use ($form) {
                    if ($form->hasFieldValue($match[1])) {
                        return $form->getFieldValue($match[1])->getValue();
                    }

                    return '';
                },
                $item
            );
        });
    }
}
