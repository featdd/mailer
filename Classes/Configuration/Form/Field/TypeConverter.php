<?php
namespace Featdd\Mailer\Configuration\Form\Field;

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

use Serializable;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\Exception\TypeConverterException;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverterInterface;

/**
 * @package Featdd\Mailer\Configuration\Form\Field
 */
class TypeConverter implements Serializable
{
    /**
     * @var \TYPO3\CMS\Extbase\Property\TypeConverterInterface
     */
    protected TypeConverterInterface $concreteTypeConverter;

    /**
     * @var \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface
     */
    protected PropertyMappingConfigurationInterface $propertyMappingConfiguration;

    /**
     * TypeConverter constructor.
     *
     * @param \TYPO3\CMS\Extbase\Property\TypeConverterInterface $concreteTypeConverter
     * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $propertyMappingConfiguration
     */
    public function __construct(TypeConverterInterface $concreteTypeConverter, PropertyMappingConfigurationInterface $propertyMappingConfiguration)
    {
        $this->concreteTypeConverter = $concreteTypeConverter;
        $this->propertyMappingConfiguration = $propertyMappingConfiguration;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function convertValue(mixed $value): mixed
    {
        try {
            $convertedValue = $this->concreteTypeConverter->convertFrom(
                $value,
                $this->concreteTypeConverter->getSupportedTargetType(),
                [],
                $this->propertyMappingConfiguration
            );
        } catch (TypeConverterException) {
            $convertedValue = null;
        }

        return $convertedValue;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            'concreteTypeConverter' => get_class($this->concreteTypeConverter),
            'propertyMappingConfiguration' => serialize($this->propertyMappingConfiguration),
        ]);
    }

    /**
     * @param string $data
     */
    public function unserialize(string $data)
    {
        $data = unserialize($data);
        $this->concreteTypeConverter = GeneralUtility::makeInstance($data['concreteTypeConverter']);
        $this->propertyMappingConfiguration = unserialize($data['propertyMappingConfiguration']);
    }
}
