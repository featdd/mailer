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

use Featdd\Mailer\Configuration\Exception as ConfigurationException;
use Featdd\Mailer\Domain\Model\Form;
use ReflectionClass;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Http\ResponseFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * @package Featdd\Mailer\Finisher
 */
class RedirectFinisher extends AbstractFinisher
{
    /**
     * @var bool
     */
    protected bool $isXhrCapable = false;

    /**
     * @param \Featdd\Mailer\Domain\Model\Form $form
     * @throws \Featdd\Mailer\Configuration\Exception
     * @throws \TYPO3\CMS\Core\Http\PropagateResponseException
     */
    public function process(Form $form): void
    {
        $statusCode = $this->options['statusCode'] ?? HttpUtility::HTTP_STATUS_302;

        if (true === is_numeric($statusCode)) {
            $httpUtilityReflection = new ReflectionClass(HttpUtility::class);

            if (true === $httpUtilityReflection->hasConstant('HTTP_STATUS_' . $statusCode)) {
                $statusCode = $httpUtilityReflection->getConstant('HTTP_STATUS_' . $statusCode);
            } else {
                throw new ConfigurationException('Invalid status code for redirect finisher');
            }
        }

        $target = $this->options['target'];

        if (true === empty($target)) {
            throw new ConfigurationException('Missing target for redirect finisher');
        }

        if (true === is_numeric($target)) {
            $target = 't3://page?uid=' . $target;
        }

        $target = GeneralUtility::makeInstance(ContentObjectRenderer::class)->typoLink_URL([
            'parameter' => $target,
            'forceAbsoluteUrl' => true,
        ]);

        /** @var \TYPO3\CMS\Core\Http\ResponseFactory $responseFactory */
        $responseFactory = GeneralUtility::makeInstance(ResponseFactory::class);

        $redirectResponse = $responseFactory
            ->createResponse($statusCode)
            ->withAddedHeader('location', $target);

        throw new PropagateResponseException($redirectResponse);
    }
}
