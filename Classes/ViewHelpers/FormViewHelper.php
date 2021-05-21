<?php
declare(strict_types=1);

namespace Featdd\Mailer\ViewHelpers;

use Featdd\Mailer\Domain\Model\Form;
use Featdd\Mailer\Utility\PageUtility;
use Featdd\Mailer\Utility\SettingsUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;

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
 * @package Featdd\Mailer\ViewHelpers
 */
class FormViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper
{
    /**
     * @var \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext
     */
    protected $renderingContext;

    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
        $this->registerArgument('object', Form::class, 'The form object to use', true);
        $this->registerArgument('additionalAttributes', 'array', 'Additional tag attributes. They will be added directly to the resulting HTML tag.');
        $this->registerArgument('javaScriptConfiguration', 'array', 'Configuration for Mailer JavaScript', false, []);
        $this->registerTagAttribute('enctype', 'string', 'MIME type with which the form is submitted');
    }

    public function render(): string
    {
        $this->arguments['objectName'] = 'form';
        $this->arguments['action'] = 'submit';
        $this->arguments['controller'] = 'Form';
        $this->arguments['pluginName'] = 'Form';
        $this->arguments['pageUid'] = PageUtility::currentPageUid();

        $this->tag->addAttribute(
            'class',
            true === $this->tag->hasAttribute('class')
                ? 'mailer ' . $this->tag->getAttribute('class')
                : 'mailer'
        );

        if (0 < count($this->arguments['javaScriptConfiguration'] ?? [])) {
            $this->tag->addAttribute('data-configuration', json_encode($this->arguments['javaScriptConfiguration']));
        }

        $controllerContext = $this->renderingContext->getControllerContext();

        $this->tag->addAttribute('name', $this->arguments['object']->getConfiguration()->getIdentifier() . '-' . $this->arguments['object']->getContentElementUid());

        /** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uriBuilder
            ->setRequest($controllerContext->getRequest())
            ->reset()
            ->setTargetPageType((int) SettingsUtility::settings()['api']['xhr']['pageType'])
            ->setCreateAbsoluteUri(true);

        $this->tag->addAttribute('data-uri-xhr', $uriBuilder->uriFor('submit'));

        $validationResult = $controllerContext
            ->getRequest()
            ->getOriginalRequestMappingResults();

        if ($validationResult instanceof Result) {
            $validationResult = $validationResult->forProperty('form');
        }

        $this->templateVariableContainer->add('validationResult', $validationResult);

        return parent::render();
    }

    /**
     * @return string
     */
    protected function renderHiddenReferrerFields(): string
    {
        $identifier = GeneralUtility::makeInstance(HashService::class)->appendHmac(
            base64_encode(
                $this->arguments['object']->getConfiguration()->getIdentifier() .
                '|' .
                $this->arguments['object']->getContentElementUid()
            )
        );

        $result = parent::renderHiddenReferrerFields();
        $result .= '<input type="hidden" name="' . $this->prefixFieldName($this->arguments['objectName'] . '[' . 'identifier' . ']') . '" value="' . $identifier . '" />' . LF;

        return $result;
    }
}
