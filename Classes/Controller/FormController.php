<?php
namespace Featdd\Mailer\Controller;

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
use Featdd\Mailer\Exception;
use Featdd\Mailer\Finisher\Exception as FinisherException;
use Featdd\Mailer\Property\TypeConverter\Exception as TypeConverterException;
use Featdd\Mailer\Service\SessionService;
use Featdd\Mailer\Utility\PageUtility;
use Featdd\Mailer\Utility\PathUtility;
use Featdd\Mailer\View\TemplateView;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Property\Exception as PropertyException;
use TYPO3\CMS\Extbase\Service\CacheService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @package Featdd\Mailer\Controller
 */
class FormController extends ActionController
{
    public const SESSION_KEY_SUBMITTED_FORMS = 'SUBMITTED_FORMS';

    /**
     * @var string
     */
    protected $defaultViewObjectName = TemplateView::class;

    /**
     * @var \Featdd\Mailer\View\TemplateView
     */
    protected $view;

    /**
     * @var bool
     */
    protected bool $isXhrRequest = false;

    /**
     * @var int
     */
    protected int $contentElementUid;

    /**
     * @var \Featdd\Mailer\Service\SessionService
     */
    protected SessionService $sessionService;

    /**
     * @var \Featdd\Mailer\Exception|null
     */
    protected static ?Exception $previousError = null;

    /**
     * @param \Featdd\Mailer\Service\SessionService $sessionService
     */
    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function initializeAction()
    {
        parent::initializeAction();

        $this->isXhrRequest = (int) $this->settings['api']['xhr']['pageType'] === PageUtility::currentPageType();
        $this->contentElementUid = $this->configurationManager->getContentObject()->data['uid'];
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     */
    public function initializeView(ViewInterface $view): void
    {
        parent::initializeView($view);

        $this->view->assign('data', $this->configurationManager->getContentObject()->data);
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function renderAction(): ResponseInterface
    {
        try {
            if (self::$previousError instanceof Exception) {
                throw self::$previousError;
            }

            $form = Form::create($this->configurationManager->getContentObject()->data['mailer_form'] ?? '', $this->configurationManager->getContentObject()->data['uid']);
            $this->view->setTemplatePathAndFilename($form->getConfiguration()->getFormTemplate());
            $this->view->assignMultiple($form->getConfiguration()->getTemplateVariables());

            $submittedForms = $this->sessionService->getKey(self::SESSION_KEY_SUBMITTED_FORMS) ?? [];

            $this->view->assignMultiple([
                'form' => $form,
                'submitted' => true === in_array($form->getConfiguration()->getIdentifier(), $submittedForms),
            ]);

            if (
                false === $form->getConfiguration()->isMultipleDispatchAllowed() &&
                true === in_array($form->getConfiguration()->getIdentifier(), $submittedForms)
            ) {
                $this->view->setTemplatePathAndFilename($form->getConfiguration()->getSubmitTemplate());
            }
        } catch (Exception $exception) {
            $this->view->assign('message', $exception->getMessage());
            $this->view->setTemplatePathAndFilename(
                true === Environment::getContext()->isDevelopment()
                    ? PathUtility::resolveAbsoluteTemplatePath('Error/ErrorDevelopment')
                    : PathUtility::resolveAbsoluteTemplatePath('Error/Error')
            );
        }

        return $this->htmlResponse();
    }

    /**
     * @param \Featdd\Mailer\Domain\Model\Form $form
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @Extbase\Validate(param="form", validator="Featdd\Mailer\Validation\Validator\FormValidator")
     */
    public function submitAction(Form $form): ResponseInterface
    {
        $submittedForms = $this->sessionService->getKey(self::SESSION_KEY_SUBMITTED_FORMS) ?? [];

        if (false === $form->getConfiguration()->isMultipleDispatchAllowed()) {
            if (true === in_array($form->getConfiguration()->getIdentifier(), $submittedForms)) {
                if (false === $this->isXhrRequest) {
                    $this->redirect('render');
                } else {
                    return (new JsonResponse([
                        'errors' => [
                            'form' => [
                                LocalizationUtility::translate('validation.already_submitted', 'mailer'),
                            ],
                        ],
                    ]))->withStatus(403);
                }
            }

            $submittedForms[] = $form->getConfiguration()->getIdentifier();
            $this->sessionService->setKey(self::SESSION_KEY_SUBMITTED_FORMS, $submittedForms);
        }

        $this->view->setTemplatePathAndFilename($form->getConfiguration()->getSubmitTemplate());

        foreach ($form->getConfiguration()->getFinisher() as $finisher) {
            if (true === $this->isXhrRequest && false === $finisher->isXhrCapable()) {
                continue;
            }

            try {
                $finisher->process($form);
            } catch (FinisherException $exception) {
                if (false === $form->getConfiguration()->isMultipleDispatchAllowed()) {
                    unset($submittedForms[array_search($form->getConfiguration()->getIdentifier(), $submittedForms)]);
                    $this->sessionService->setKey(self::SESSION_KEY_SUBMITTED_FORMS, $submittedForms);
                }

                if (true === $this->isXhrRequest) {
                    return (new JsonResponse(['errors' => ['form' => [$exception->getMessage()]]]))->withStatus(500);
                } else {
                    $this->forwardWithException($exception);
                }
            }
        }

        $this->view->assign('form', $form);

        if (true === $this->isXhrRequest) {
            return new JsonResponse(['html' => $this->view->render()]);
        }

        return $this->htmlResponse();
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function errorAction(): ResponseInterface
    {
        if (false === $this->isXhrRequest) {
            return parent::errorAction();
        }

        $response = ['errors' => []];

        $validationResult = $this->arguments->validate()->forProperty('form');

        if (0 < count($validationResult->getErrors())) {
            $response['errors']['form'] = [];

            /** @var \TYPO3\CMS\Extbase\Error\Error[] $fieldErrors */
            foreach ($validationResult->getErrors() as $formError) {

                $response['errors']['form'][] = $formError->getMessage();
            }
        }

        $response['errors']['field'] = [];

        /** @var \Featdd\Mailer\Domain\Model\Form $form */
        try {
            $form = $this->arguments->getArgument('form')->getValue();

            if ($form instanceof Form) {
                foreach ($form->getConfiguration()->getFields() as $field) {
                    $response['errors']['field'][$field->getName()] = [];
                }
            }
        } catch (NoSuchArgumentException) {
            // Ignore
        }

        if (0 < count($validationResult->getSubResults())) {
            foreach ($validationResult->getSubResults() as $property => $result) {
                if (0 < count($result->getErrors())) {
                    $response['errors']['field'][$property] = [];

                    foreach ($result->getErrors() as $error) {
                        $response['errors']['field'][$property][] = $error->getMessage();
                    }
                }
            }
        }

        return (new JsonResponse($response))->withStatus(400);
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Controller\Exception\RequiredArgumentMissingException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException
     * @throws \TYPO3\CMS\Core\Http\ImmediateResponseException
     */
    protected function mapRequestArgumentsToControllerArguments(): void
    {
        try {
            parent::mapRequestArgumentsToControllerArguments();
        } catch (PropertyException $exception) {
            if (true === $this->isXhrRequest) {
                throw new ImmediateResponseException(
                    (new JsonResponse([
                        'errors' => [
                            'form' => [
                                Environment::getContext()->isDevelopment()
                                    ? $exception->getMessage()
                                    : LocalizationUtility::translate('validation.form_error', 'mailer'),
                            ],
                        ],
                    ]))->withStatus(400)
                );
            } elseif ($exception->getPrevious() instanceof TypeConverterException) {
                $this->forwardWithException($exception->getPrevious());
            }
        }
    }

    /**
     * @param \Exception $exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException
     */
    protected function forwardWithException(Throwable $exception)
    {
        /** @var \TYPO3\CMS\Extbase\Service\CacheService $cacheService */
        $cacheService = GeneralUtility::makeInstance(CacheService::class);
        self::$previousError = $exception->getPrevious();
        $cacheService->clearPageCache([$GLOBALS['TSFE']->id]);
        $this->forwardToReferringRequest();
    }
}
