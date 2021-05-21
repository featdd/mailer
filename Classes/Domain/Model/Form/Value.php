<?php
declare(strict_types=1);

namespace Featdd\Mailer\Domain\Model\Form;

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

use TYPO3\CMS\Extbase\DomainObject\AbstractValueObject;

/**
 * @package Featdd\Mailer\Domain\Model
 */
class Value extends AbstractValueObject
{
    /**
     * @var string
     */
    protected string $fieldName = '';

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param string $fieldName
     * @param mixed $value
     */
    public function __construct(string $fieldName, $value)
    {
        $this->fieldName = $fieldName;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
