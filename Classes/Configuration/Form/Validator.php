<?php
namespace Featdd\Mailer\Configuration\Form;

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
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

/**
 * @package Featdd\Mailer\Configuration\Form
 */
class Validator
{
    /**
     * @var \TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface
     */
    protected ValidatorInterface $validator;

    /**
     * @var string
     */
    protected string $message;

    /**
     * @param \TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface $validator
     * @param string $message
     */
    public function __construct(ValidatorInterface $validator, string $message)
    {
        $this->validator = $validator;
        $this->message = $message;
    }

    /**
     * @param \Featdd\Mailer\Domain\Model\Form\Value $value
     * @return bool
     */
    public function validate(Value $value): bool
    {
        return false === $this->validator->validate($value->getValue())->hasErrors();
    }

    /**
     * @return string
     * @throws \Featdd\Mailer\Utility\Exception\LanguageServiceException
     */
    public function getMessage(): string
    {
        return TranslationUtility::translateLLL($this->message);
    }
}
