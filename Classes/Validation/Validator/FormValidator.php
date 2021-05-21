<?php
declare(strict_types=1);

namespace Featdd\Mailer\Validation\Validator;

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

use Featdd\Mailer\Domain\Model\Form\Value;
use Featdd\Mailer\Utility\TranslationUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * @package Featdd\Mailer\Validation\Validator
 */
class FormValidator extends AbstractValidator
{
    /**
     * @param \Featdd\Mailer\Domain\Model\Form $value
     * @throws \Featdd\Mailer\Utility\Exception\LanguageServiceException
     */
    protected function isValid($value): void
    {
        foreach ($value->getConfiguration()->getValidators() as $validator) {
            if (false === $validator->validate(new Value('form', $value))) {
                $this->result->addError(new Error($validator->getMessage(), time()));
            }
        }

        foreach ($value->getConfiguration()->getFields() as $field) {
            foreach ($field->getValidators() as $validator) {
                if (false === $validator->validate($value->getFieldValue($field->getName()))) {
                    $this->result
                        ->forProperty($field->getName())
                        ->addError(new Error($validator->getMessage(), time()));
                }
            }
        }
    }
}
