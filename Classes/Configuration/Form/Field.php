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

use Featdd\Mailer\Configuration\Form\Field\TypeConverter;

/**
 * @package Featdd\Mailer\Configuration\Form
 */
class Field
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var \Featdd\Mailer\Configuration\Form\Validator[]
     */
    protected array $validators;

    /**
     * @var \Featdd\Mailer\Configuration\Form\Field\TypeConverter|null
     */
    protected ?TypeConverter $typeConverter = null;

    /**
     * @param string $name
     * @param \Featdd\Mailer\Configuration\Form\Validator[] $validators
     * @param \Featdd\Mailer\Configuration\Form\Field\TypeConverter|null $typeConverter
     */
    public function __construct(string $name, array $validators, Field\TypeConverter $typeConverter = null)
    {
        $this->name = $name;
        $this->validators = $validators;
        $this->typeConverter = $typeConverter;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Featdd\Mailer\Configuration\Form\Validator[]
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * @return \Featdd\Mailer\Configuration\Form\Field\TypeConverter|null
     */
    public function getTypeConverter(): ?Field\TypeConverter
    {
        return $this->typeConverter;
    }
}
