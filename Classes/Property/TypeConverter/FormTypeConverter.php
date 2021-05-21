<?php
declare(strict_types=1);

namespace Featdd\Mailer\Property\TypeConverter;

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

use Featdd\Mailer\Configuration\Exception as ConfigurationException;
use Featdd\Mailer\Configuration\Form\Field\TypeConverter;
use Featdd\Mailer\Domain\Model\Form;
use Featdd\Mailer\Property\TypeConverter\Exception\InvalidIdentifierException;
use Featdd\Mailer\Property\TypeConverter\Exception\MissingConfigurationException;
use Featdd\Mailer\Property\TypeConverter\Exception\MissingFieldException;
use Featdd\Mailer\Property\TypeConverter\Exception\MissingIdentifierException;
use Featdd\Mailer\Service\ConfigurationService;
use Featdd\Mailer\Utility\Exception\LanguageServiceException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;
use TYPO3\CMS\Extbase\Security\Exception\InvalidHashException;

/**
 * @package Featdd\Mailer\Property\TypeConverter
 */
class FormTypeConverter extends AbstractTypeConverter
{
    /**
     * @var array
     */
    protected $sourceTypes = ['array'];

    /**
     * @var string
     */
    protected $targetType = Form::class;

    /**
     * @var int
     */
    protected $priority = 10;

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
     * @param mixed $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface|null $configuration
     * @return \Featdd\Mailer\Domain\Model\Form
     * @throws \Featdd\Mailer\Property\TypeConverter\Exception
     * @throws \Featdd\Mailer\Utility\Exception\LanguageServiceException
     */
    public function convertFrom($source, string $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null): Form
    {
        if (false === array_key_exists('identifier', $source)) {
            throw new MissingIdentifierException('Missing the identifier in posted form data');
        }

        try {
            $identifier = base64_decode(GeneralUtility::makeInstance(HashService::class)->validateAndStripHmac($source['identifier']));
        } catch (InvalidArgumentForHashGenerationException | InvalidHashException $exception) {
            throw new InvalidIdentifierException($exception->getMessage());
        }

        [$formIdentifier, $contentElementUid] = GeneralUtility::trimExplode('|', $identifier, true);

        try {
            $configuration = $this->configurationService->loadConfiguration($formIdentifier);
        } catch (ConfigurationException $exception) {
            throw new MissingConfigurationException($exception->getMessage());
        }

        if (0 >= (int) $contentElementUid) {
            throw new InvalidIdentifierException('Missing the uid in the identifier');
        }

        $values = [];

        foreach ($configuration->getFields() as $field) {
            if (false === array_key_exists($field->getName(), $source)) {
                throw new MissingFieldException('Missing field "' . $field->getName() . '" in post data');
            }

            $values[$field->getName()] = new Form\Value(
                $field->getName(),
                $field->getTypeConverter() instanceof TypeConverter
                    ? $field->getTypeConverter()->convertValue($source[$field->getName()])
                    : $source[$field->getName()]
            );
        }

        return new Form($configuration, (int) $contentElementUid, $values);
    }
}
