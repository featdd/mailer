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

use Featdd\Mailer\Domain\Model\Form;

/**
 * @package Featdd\Mailer\Finisher
 */
interface FinisherInterface
{
    public const TCA_PALETTE_LINEBREAK = '--linebreak--';

    /**
     * @param array $options
     */
    public function __construct(array $options = []);

    /**
     * @return string|null
     */
    public static function tcaPaletteKey(): ?string;

    /**
     * @return string|null
     */
    public static function tcaPaletteLabel(): ?string;

    /**
     * @return string[]|null
     */
    public static function tcaPaletteShowItem(): ?array;

    /**
     * @return array|null
     */
    public static function tcaColumns(): ?array;

    /**
     * @return array|null
     */
    public static function sqlColumns(): ?array;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @return bool
     */
    public function isXhrCapable(): bool;

    /**
     * @param \Featdd\Mailer\Domain\Model\Form $form
     */
    public function process(Form $form): void;
}
