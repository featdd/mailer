<?php
declare(strict_types=1);

namespace Featdd\Mailer\View;

use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;

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
 * @package Featdd\Mailer\View
 */
class TemplateView extends \TYPO3\CMS\Fluid\View\TemplateView
{
    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext $controllerContext
     * @return bool
     */
    public function canRender(ControllerContext $controllerContext): bool
    {
        return true;
    }
}
