<?php
declare(strict_types=1);

namespace Featdd\Mailer\Preview;

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

use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;

/**
 * @package Featdd\Mailer\Preview
 */
class MailerFormPreviewRenderer extends StandardContentPreviewRenderer
{
    /**
     * @param \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem $item
     * @return string
     */
    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {
        return parent::renderPageModulePreviewHeader($item);
    }

    /**
     * @param \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem $item
     * @return string
     */
    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        return '<p>TODO: generate a nice preview of the form</p>';
    }

    /**
     * @param \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem $item
     * @return string
     */
    public function renderPageModulePreviewFooter(GridColumnItem $item): string
    {
        return parent::renderPageModulePreviewFooter($item);
    }

    /**
     * @param string $previewHeader
     * @param string $previewContent
     * @param \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem $item
     * @return string
     */
    public function wrapPageModulePreview(string $previewHeader, string $previewContent, GridColumnItem $item): string
    {
        return $content = '<span class="mailer-form-preview">' . $previewHeader . $previewContent . '</span>';
    }
}
