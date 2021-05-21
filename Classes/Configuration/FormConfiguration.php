<?php
namespace Featdd\Mailer\Configuration;

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

use Featdd\Mailer\Utility\TranslationUtility;

/**
 * @package Featdd\Mailer\Configuration
 */
class FormConfiguration
{
    /**
     * @var string
     */
    protected string $identifier = '';

    /**
     * @var string
     */
    protected string $formTemplate = '';

    /**
     * @var string
     */
    protected string $submitTemplate = '';

    /**
     * @var array
     */
    protected $templateVariables = [];

    /**
     * @var bool
     */
    protected bool $multipleDispatchAllowed = false;

    /**
     * @var string
     */
    protected string $wizardTitle = '';

    /**
     * @var string
     */
    protected string $wizardDescription = '';

    /**
     * @var string
     */
    protected string $wizardIconIdentifier = '';

    /**
     * @var \TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface[]
     */
    protected array $validators;

    /**
     * @var \Featdd\Mailer\Configuration\Form\Field[]
     */
    protected array $fields;

    /**
     * @var \Featdd\Mailer\Finisher\FinisherInterface[]
     */
    protected array $finisher;

    /**
     * @param string $identifier
     * @param string $formTemplate
     * @param string $submitTemplate
     * @param array|string $templateVariables
     * @param bool $multipleDispatchAllowed
     * @param string $wizardTitle
     * @param string $wizardDescription
     * @param string $wizardIconIdentifier
     * @param \Featdd\Mailer\Configuration\Form\Validator[] $validators
     * @param \Featdd\Mailer\Configuration\Form\Field[] $fields
     * @param \Featdd\Mailer\Finisher\FinisherInterface[] $finisher
     */
    public function __construct(
        string $identifier,
        string $formTemplate,
        string $submitTemplate,
        array $templateVariables,
        bool $multipleDispatchAllowed,
        string $wizardTitle,
        string $wizardDescription,
        string $wizardIconIdentifier,
        array $validators,
        array $fields,
        array $finisher
    ) {
        $this->identifier = $identifier;
        $this->formTemplate = $formTemplate;
        $this->submitTemplate = $submitTemplate;
        $this->templateVariables = $templateVariables;
        $this->multipleDispatchAllowed = $multipleDispatchAllowed;
        $this->wizardTitle = $wizardTitle;
        $this->wizardDescription = $wizardDescription;
        $this->wizardIconIdentifier = $wizardIconIdentifier;
        $this->validators = $validators;
        $this->fields = $fields;
        $this->finisher = $finisher;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getFormTemplate(): string
    {
        return $this->formTemplate;
    }

    /**
     * @return string
     */
    public function getSubmitTemplate(): string
    {
        return $this->submitTemplate;
    }

    /**
     * @param bool $translate
     * @return array
     * @throws \Featdd\Mailer\Utility\Exception\LanguageServiceException
     */
    public function getTemplateVariables(bool $translate = true): array
    {
        $templateVariables = $this->templateVariables;

        if (true === $translate) {
            array_walk_recursive($templateVariables, function (&$item) {
                if (true === is_string($item)) {
                    $item = TranslationUtility::translateLLL($item);
                }
            });
        }

        return $templateVariables;
    }

    /**
     * @return bool
     */
    public function isMultipleDispatchAllowed(): bool
    {
        return $this->multipleDispatchAllowed;
    }

    /**
     * @return string
     */
    public function getWizardTitle(): string
    {
        return $this->wizardTitle;
    }

    /**
     * @return string
     */
    public function getWizardDescription(): string
    {
        return $this->wizardDescription;
    }

    /**
     * @return string
     */
    public function getWizardIconIdentifier(): string
    {
        return $this->wizardIconIdentifier;
    }

    /**
     * @return \Featdd\Mailer\Configuration\Form\Validator[]
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * @return \Featdd\Mailer\Configuration\Form\Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return \Featdd\Mailer\Finisher\FinisherInterface[]
     */
    public function getFinisher(): array
    {
        return $this->finisher;
    }
}
