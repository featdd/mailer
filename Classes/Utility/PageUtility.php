<?php
declare(strict_types=1);

namespace Featdd\Mailer\Utility;

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
 * @package Featdd\Mailer\Utility
 */
class PageUtility
{
    /**
     * @return int
     */
    public static function currentPageType(): int
    {
        return (int) $GLOBALS['TSFE']->type;
    }

    /**
     * @return int
     */
    public static function currentPageUid(): int
    {
        return (int) $GLOBALS['TSFE']->id;
    }
}
