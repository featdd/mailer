<?php
declare(strict_types=1);

namespace Featdd\Mailer\Form\FormDataProvider;

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

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

/**
 * @package Featdd\Mailer\Form\FormDataProvider
 */
class MailerFormFields implements FormDataProviderInterface
{
    /**
     * @param array $result
     * @return array
     */
    public function addData(array $result): array
    {
        // TODO: add dynamic field values to form data
        // $result['databaseRow']['sometestfield'] = 'foo';

        return $result;
    }
}
