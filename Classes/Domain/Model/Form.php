<?php
declare(strict_types=1);

namespace Featdd\Mailer\Domain\Model;

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

use ArrayAccess;
use Featdd\Mailer\Configuration\FormConfiguration;
use Featdd\Mailer\Domain\Model\Form\Value;
use Featdd\Mailer\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractValueObject;

/**
 * @package Featdd\Mailer\Domain\Model
 */
class Form extends AbstractValueObject implements ArrayAccess
{
    /**
     * @var \Featdd\Mailer\Configuration\FormConfiguration
     */
    protected FormConfiguration $configuration;

    /**
     * @var int
     */
    protected int $contentElementUid;

    /**
     * @var \Featdd\Mailer\Domain\Model\Form\Value[]
     */
    protected array $values;

    /**
     * @param \Featdd\Mailer\Configuration\FormConfiguration $configuration
     * @param int $contentElementUid
     * @param \Featdd\Mailer\Domain\Model\Form\Value[] $values
     */
    public function __construct(FormConfiguration $configuration, int $contentElementUid, array $values = [])
    {
        $this->configuration = $configuration;
        $this->contentElementUid = $contentElementUid;
        $this->values = $values;
    }

    /**
     * @param string $formIdentifier
     * @param int $contentElementUid
     * @return \Featdd\Mailer\Domain\Model\Form
     * @throws \Featdd\Mailer\Exception
     */
    public static function create(string $formIdentifier, int $contentElementUid): Form
    {
        /** @var ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

        return new self($configurationService->loadConfiguration($formIdentifier), $contentElementUid);
    }

    /**
     * @return \Featdd\Mailer\Configuration\FormConfiguration
     */
    public function getConfiguration(): FormConfiguration
    {
        return $this->configuration;
    }

    /**
     * @return int
     */
    public function getContentElementUid(): int
    {
        return $this->contentElementUid;
    }

    /**
     * @param string $fieldName
     * @return bool
     */
    public function hasFieldValue(string $fieldName): bool
    {
        return true === array_key_exists($fieldName, $this->values);
    }

    /**
     * @param string $fieldName
     * @return \Featdd\Mailer\Domain\Model\Form\Value
     */
    public function getFieldValue(string $fieldName): ?Value
    {
        return $this->values[$fieldName] ?? null;
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return true === array_key_exists($offset, $this->values);
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if ($this->hasFieldValue($offset)) {
            return $this->getFieldValue($offset)->getValue();
        }

        return null;
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->values[$offset] = new Value($offset, $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->values[$offset]);
    }
}
