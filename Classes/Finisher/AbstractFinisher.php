<?php
declare(strict_types=1);

namespace Featdd\Mailer\Finisher;

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

/**
 * @package Featdd\Mailer\Finisher
 */
abstract class AbstractFinisher implements FinisherInterface
{
    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var bool
     */
    protected bool $isXhrCapable = true;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @return string
     */
    public static function tcaPaletteKey(): ?string
    {
        $classNamespace = explode('\\', static::class);
        $className = array_pop($classNamespace);

        return 'mailer_' . strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $className));
    }

    /**
     * @return string|null
     */
    public static function tcaPaletteLabel(): ?string
    {
        return null;
    }

    /**
     * @return string[]
     */
    public static function tcaPaletteShowItem(): ?array
    {
        $tcaColumns = static::tcaColumns();

        if (true === is_array($tcaColumns)) {
            $columnNames = [];
            $columnCount = count($tcaColumns);

            foreach (array_keys($tcaColumns) as $index => $columnName) {
                $columnNames[] = $columnName;

                if (($index + 1) < $columnCount) {
                    $columnNames[] = FinisherInterface::TCA_PALETTE_LINEBREAK;
                }
            }

            return $columnNames;
        }

        return null;
    }

    /**
     * @return array
     */
    public static function tcaColumns(): ?array
    {
        return null;
    }

    /**
     * @return array|null
     */
    public static function sqlColumns(): ?array
    {
        return null;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return bool
     */
    public function isXhrCapable(): bool
    {
        return $this->isXhrCapable;
    }
}
