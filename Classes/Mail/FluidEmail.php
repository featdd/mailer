<?php
declare(strict_types=1);

namespace Featdd\Mailer\Mail;

use TYPO3\CMS\Core\Mail\FluidEmail as CoreFluidEmail;

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
class FluidEmail extends CoreFluidEmail
{
    /**
     * @var string
     */
    protected $templatePath;

    /**
     * @param string $templatePath
     * @return \Featdd\Mailer\Mail\FluidEmail
     */
    public function setTemplatePath(string $templatePath): self
    {
        $this->templatePath = $templatePath;

        return $this;
    }

    /**
     * @param string $format
     * @return string
     */
    public function renderContent(string $format): string
    {
        if (true === empty($this->templatePath)) {
            return parent::renderContent($format);
        }

        $this->view->setTemplateRootPaths([pathinfo($this->templatePath, PATHINFO_DIRNAME)]);
        $this->view->setTemplate(pathinfo($this->templatePath, PATHINFO_FILENAME));
        $this->view->setFormat($format);

        return $this->view->render();
    }
}
