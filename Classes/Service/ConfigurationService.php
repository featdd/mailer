<?php
declare(strict_types=1);

namespace Featdd\Mailer\Service;

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

use Featdd\Mailer\Configuration;
use Featdd\Mailer\Finisher\FinisherInterface;
use Featdd\Mailer\Utility\Exception\LanguageServiceException;
use Featdd\Mailer\Utility\PathUtility;
use Featdd\Mailer\Utility\TranslationUtility;
use InvalidArgumentException;
use TYPO3\CMS\Core\Cache\Exception\InvalidDataException;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverterInterface;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * @package Featdd\Mailer\Service
 */
class ConfigurationService implements SingletonInterface
{
    public const CACHE_KEY_CONFIGURATIONS = 'mailer_configurations';
    public const IDENTIFIER_SEPERATOR = '/';
    public const EXTBASE_TYPECONVERTER_NAMESPACE = 'TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\';

    /**
     * @var \TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader
     */
    protected YamlFileLoader $yamlFileLoader;

    /**
     * @var \TYPO3\CMS\Core\Package\PackageManager
     */
    protected PackageManager $packageManager;

    /**
     * @var \TYPO3\CMS\Extbase\Validation\ValidatorResolver
     */
    protected ValidatorResolver $validatorResolver;

    /**
     * @var \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend
     */
    protected PhpFrontend $cache;

    /**
     * @param \TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader $yamlFileLoader
     * @param \TYPO3\CMS\Core\Package\PackageManager $packageManager
     * @param \TYPO3\CMS\Extbase\Validation\ValidatorResolver $validatorResolver
     * @param \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend $cache
     */
    public function __construct(
        YamlFileLoader $yamlFileLoader,
        PackageManager $packageManager,
        ValidatorResolver $validatorResolver,
        PhpFrontend $cache
    ) {
        $this->yamlFileLoader = $yamlFileLoader;
        $this->packageManager = $packageManager;
        $this->validatorResolver = $validatorResolver;
        $this->cache = $cache;
    }

    /**
     * @param string $formIdentifier
     * @return \Featdd\Mailer\Configuration\FormConfiguration
     * @throws \Featdd\Mailer\Configuration\Exception
     * @throws \Featdd\Mailer\Utility\Exception\LanguageServiceException
     */
    public function loadConfiguration(string $formIdentifier): Configuration\FormConfiguration
    {
        [$extensionKey, $configurationFile] = explode(self::IDENTIFIER_SEPERATOR, $formIdentifier ?? '');

        if (
            true === empty($extensionKey) ||
            true === empty($configurationFile)
        ) {
            throw new Configuration\Form\Exception\IdentifierException('Invalid identifier "' . $formIdentifier . '"');
        }

        $cacheIdentifier = $extensionKey . '_' . $configurationFile . '_' . TranslationUtility::currentLanguageIsoCode();

        try {
            $hasCachedConfiguration = $this->cache->has($cacheIdentifier);
        } catch (InvalidArgumentException) {
            $hasCachedConfiguration = false;
        }

        if (true === $hasCachedConfiguration) {
            $configuration = unserialize($this->cache->require($cacheIdentifier));
        } else {
            try {
                $package = $this->packageManager->getPackage($extensionKey);
            } catch (UnknownPackageException) {
                throw new Configuration\Form\Exception\DoesNotExistException('The extension "' . $extensionKey . '" does not exist');
            }

            if (false === $this->packageManager->isPackageActive($package->getPackageKey())) {
                throw new Configuration\Form\Exception\DoesNotExistException('The extension "' . $package->getPackageKey() . '" is not installed');
            }

            $packageMailerFormConfiguration = $package->getPackagePath() . 'Configuration/Forms/' . $configurationFile . '.yaml';

            if (false === file_exists($packageMailerFormConfiguration)) {
                throw new Configuration\Form\Exception\DoesNotExistException(
                    'The configuration file "' . $packageMailerFormConfiguration . '" could not be loaded'
                );
            }

            $configuration = $this->yamlFileLoader->load($packageMailerFormConfiguration);

            $this->processConfiguration($configuration);

            $validators = $this->loadValidators($configuration['validators'] ?? []);
            $fields = $this->loadFields($configuration['fields'] ?? []);
            $finisher = $this->loadFinisher($configuration['finisher'] ?? []);

            $configuration = new Configuration\FormConfiguration(
                $formIdentifier,
                $configuration['templates']['form'],
                $configuration['templates']['submit'],
                $configuration['templates']['variables'],
                $configuration['multipleDispatchAllowed'],
                $configuration['wizard']['title'] ?? '',
                $configuration['wizard']['description'] ?? '',
                $configuration['wizard']['icon'] ?? 'content-form',
                $validators,
                $fields,
                $finisher
            );

            try {
                $this->cache->set($cacheIdentifier, 'return \'' . serialize($configuration) . '\';');
            } catch (InvalidDataException $exception) {
                throw new Configuration\Form\Exception\CacheException($exception->getMessage());
            }
        }

        return $configuration;
    }

    /**
     * @return \Featdd\Mailer\Configuration\FormConfiguration[]
     */
    public function loadAllConfigurations(): array
    {
        $configurations = [];

        foreach ($this->packageManager->getActivePackages() as $package) {
            $packageFormConfigurationsPath = $package->getPackagePath() . 'Configuration/Forms';

            if (true === is_dir($packageFormConfigurationsPath)) {
                foreach (glob($packageFormConfigurationsPath . '/*.yaml') as $formConfigurationFile) {
                    $identifier = $package->getPackageKey() . '/' . basename($formConfigurationFile, '.yaml');

                    try {
                        $configurations[$identifier] = $this->loadConfiguration($identifier);
                    } catch (Configuration\Exception|LanguageServiceException) {
                        // Ignore broken configurations
                    }
                }
            }
        }

        return $configurations;
    }

    public function registerAllFormConfigurationBackendWizards(): void
    {
        $this->backendWizardRegistration(
            'default',
            'LLL:EXT:mailer/Resources/Private/Language/locallang.xlf:wizard.default.title',
            'LLL:EXT:mailer/Resources/Private/Language/locallang.xlf:wizard.default.description',
            'content-form'
        );

        foreach ($this->loadAllConfigurations() as $configuration) {
            if (false === empty($configuration->getWizardTitle())) {
                $this->backendWizardRegistration(
                    $configuration->getIdentifier(),
                    $configuration->getWizardTitle(),
                    $configuration->getWizardDescription(),
                    $configuration->getWizardIconIdentifier()
                );
            }
        }
    }

    /**
     * @param string $formIdentifier
     * @param string $title
     * @param string $description
     * @param string $iconIdentifier
     */
    protected function backendWizardRegistration(
        string $formIdentifier,
        string $title,
        string $description,
        string $iconIdentifier
    ): void {
        $formIdentifierHash = hash('sha256', $formIdentifier);

        ExtensionManagementUtility::addPageTSConfig('
            mod.wizards.newContentElement.wizardItems.forms {
              show := addToList(mailer_form_' . $formIdentifierHash . ')
              elements {
                mailer_form_' . $formIdentifierHash . ' {
                  iconIdentifier = ' . $iconIdentifier . '
                  title = ' . $title . '
                  description = ' . $description . '
                  tt_content_defValues {
                    CType = mailer_form
                    ' . ('default' !== $formIdentifier ? 'mailer_form = ' . $formIdentifier : '') . '
                  }
                }
              }
            }
        ');
    }

    /**
     * @param array $configuration
     * @throws \Featdd\Mailer\Configuration\Exception
     */
    protected function processConfiguration(array &$configuration): void
    {
        if (false === array_key_exists('templates', $configuration)) {
            throw new Configuration\Form\Exception\MissingParameterException('Missing templates in configuration');
        }

        if (false === array_key_exists('form', $configuration['templates'])) {
            throw new Configuration\Form\Exception\MissingParameterException('Missing form template in configuration');
        }

        $configuration['templates']['form'] = PathUtility::resolveAbsoluteTemplatePath($configuration['templates']['form']);

        if (null === $configuration['templates']['form']) {
            throw new Configuration\Form\Exception\InvalidParameterException('Invalid form template path in configuration');
        }

        if (false === array_key_exists('submit', $configuration['templates'])) {
            throw new Configuration\Form\Exception\MissingParameterException('Missing submit template in configuration');
        }

        $configuration['templates']['submit'] = PathUtility::resolveAbsoluteTemplatePath($configuration['templates']['submit']);

        if (null === $configuration['templates']['submit']) {
            throw new Configuration\Form\Exception\InvalidParameterException('Invalid submit path in configuration');
        }

        if (false === array_key_exists('variables', $configuration['templates'])) {
            $configuration['templates']['variables'] = [];
        } elseif (false === is_array($configuration['templates']['variables'])) {
            throw new Configuration\Form\Exception\InvalidParameterException('Invalid type "' . get_class($configuration['templates']['variables']) . '" for template variables');
        }

        if (false === array_key_exists('multipleDispatchAllowed', $configuration)) {
            $configuration['multipleDispatchAllowed'] = true;
        } else {
            $configuration['multipleDispatchAllowed'] = (bool) $configuration['multipleDispatchAllowed'];
        }

        array_walk_recursive($configuration, function (&$item) use ($configuration) {
            if (true === is_string($item)) {
                if (str_starts_with($item, 't3://')) {
                    $item = GeneralUtility::makeInstance(ContentObjectRenderer::class)->typoLink_URL([
                        'parameter' => $item,
                        'forceAbsoluteUrl' => true,
                    ]);
                }

                try {
                    if (str_contains($item, '->')) {
                        $item = GeneralUtility::callUserFunction($item, $configuration, $this);
                    }
                } catch (InvalidArgumentException $exception) {
                    // nothing
                }
            }
        });
    }

    /**
     * @param array $validatorConfigurations
     * @return \Featdd\Mailer\Configuration\Form\Validator[]
     * @throws \Featdd\Mailer\Configuration\Exception
     */
    protected function loadValidators(array $validatorConfigurations): array
    {
        $validators = [];

        foreach ($validatorConfigurations as $validatorConfiguration) {
            if (false === array_key_exists('validator', $validatorConfiguration)) {
                throw new Configuration\Form\Exception\MissingParameterException('Missing validator');
            }

            if (false === is_array($validatorConfiguration['options'] ?? null)) {
                $validatorConfiguration['options'] = [];
            }

            if (false === is_string($validatorConfiguration['message'] ?? null)) {
                $validatorConfiguration['message'] = '';
            }

            $validator = $this->validatorResolver->createValidator(
                $validatorConfiguration['validator'],
                $validatorConfiguration['options']
            );

            if (null === $validator) {
                throw new Configuration\Form\Exception\InvalidParameterException('The class "' . $validatorConfiguration['validator'] . '" is not a valid validator');
            }

            $validators[] = new Configuration\Form\Validator($validator, $validatorConfiguration['message']);
        }

        return $validators;
    }

    /**
     * @param array $fieldsConfiguration
     * @return \Featdd\Mailer\Configuration\Form\Field[]
     * @throws \Featdd\Mailer\Configuration\Exception
     */
    protected function loadFields(array $fieldsConfiguration): array
    {
        $fields = [];

        foreach ($fieldsConfiguration as $fieldName => $fieldConfiguration) {
            $typeConverter = null;

            if (true === is_array($fieldConfiguration['typeConverter'] ?? null)) {
                if (false === array_key_exists('class', $fieldConfiguration['typeConverter'])) {
                    throw new Configuration\Form\Exception\MissingParameterException('Missing class parameter for field TypeConverter');
                } elseif (true === class_exists(self::EXTBASE_TYPECONVERTER_NAMESPACE . $fieldConfiguration['typeConverter']['class'])) {
                    $fieldConfiguration['typeConverter']['class'] = self::EXTBASE_TYPECONVERTER_NAMESPACE . $fieldConfiguration['typeConverter']['class'];
                } elseif (false === class_exists($fieldConfiguration['typeConverter']['class'])) {
                    throw new Configuration\Form\Exception\InvalidParameterException('TypeConverter class "' . $fieldConfiguration['typeConverter']['class'] . '" was not found');
                } elseif (false === array_key_exists(TypeConverterInterface::class, class_implements($fieldConfiguration['typeConverter']['class']))) {
                    throw new Configuration\Form\Exception\InvalidParameterException('TypeConverter class "' . $fieldConfiguration['typeConverter']['class'] . '" is not a valid TypeConverter');
                }

                /** @var \TYPO3\CMS\Extbase\Property\TypeConverterInterface $concreteTypeConverter */
                $concreteTypeConverter = GeneralUtility::makeInstance($fieldConfiguration['typeConverter']['class']);

                /** @var \TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration $propertyMappingConfiguration */
                $propertyMappingConfiguration = GeneralUtility::makeInstance(PropertyMappingConfiguration::class);
                $propertyMappingConfiguration->setTypeConverterOptions(
                    $fieldConfiguration['typeConverter']['class'],
                    $fieldConfiguration['typeConverter']['options'] ?? []
                );

                $typeConverter = new Configuration\Form\Field\TypeConverter($concreteTypeConverter, $propertyMappingConfiguration);
            }

            $fields[] = new Configuration\Form\Field(
                $fieldName,
                $this->loadValidators($fieldConfiguration['validators'] ?? []),
                $typeConverter
            );
        }

        return $fields;
    }

    /**
     * @param array $finisherConfiguration
     * @return \Featdd\Mailer\Finisher\FinisherInterface[]
     * @throws \Featdd\Mailer\Configuration\Exception
     */
    protected function loadFinisher(array $finisherConfiguration): array
    {
        $finisher = [];

        foreach ($finisherConfiguration as $finisherConfigurationItem) {
            if (false === array_key_exists('class', $finisherConfigurationItem)) {
                throw new Configuration\Form\Exception\MissingParameterException('Missing class parameter for finisher');
            } elseif (false === class_exists($finisherConfigurationItem['class'])) {
                throw new Configuration\Form\Exception\InvalidParameterException('Finisher class "' . $finisherConfigurationItem['class'] . '" was not found');
            } elseif (false === array_key_exists(FinisherInterface::class, class_implements($finisherConfigurationItem['class']))) {
                throw new Configuration\Form\Exception\InvalidParameterException('Finisher class "' . $finisherConfigurationItem['class'] . '" is not a valid finisher');
            }

            $finisher[] = GeneralUtility::makeInstance(
                $finisherConfigurationItem['class'],
                $finisherConfigurationItem['options'] ?? []
            );
        }

        return $finisher;
    }
}
