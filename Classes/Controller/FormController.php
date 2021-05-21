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

use Featdd\Mailer\Configuration\Exception as ConfigurationException;
use Featdd\Mailer\Domain\Model\Form;
use Featdd\Mailer\Exception;
use Featdd\Mailer\Finisher\Exception as FinisherException;
use Featdd\Mailer\Property\TypeConverter\Exception as TypeConverterException;
use Featdd\Mailer\Service\SessionService;
use Featdd\Mailer\Utility\PageUtility;
use Featdd\Mailer\Utility\PathUtility;
use Featdd\Mailer\Utility\SettingsUtility;
use Featdd\Mailer\View\TemplateView;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Property\Exception as PropertyException;
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
     * @var \Mindshape\MindshapeMailer\View\TemplateView
     */
    protected $view;

    /**
     * @var bool
     */
    protected $isXhrRequest = false;

    /**
     * @var int
     */
    protected $contentElementUid;

    /**
     * @var \Featdd\Mailer\Service\SessionService
     */
    protected $sessionService;

    /**
     * @var \Featdd\Mailer\Exception
     */
    protected static $previousError;

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

    public function renderAction(): void
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
        } catch (ConfigurationException | Exception $exception) {
            $this->view->assign('message', $exception->getMessage());
            $this->view->setTemplatePathAndFilename(
                true === Environment::getContext()->isDevelopment()
                    ? PathUtility::resolveAbsoluteTemplatePath('Error/ErrorDevelopment')
                    : PathUtility::resolveAbsoluteTemplatePath('Error/Error')
            );
        }
    }

    /**
     * @param \Featdd\Mailer\Domain\Model\Form $form
     * @return string
     * @Extbase\Validate(param="form", validator="Featdd\Mailer\Validation\Validator\FormValidator")
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function submitAction(Form $form): string
    {
        $submittedForms = $this->sessionService->getKey(self::SESSION_KEY_SUBMITTED_FORMS) ?? [];

        if (false === $form->getConfiguration()->isMultipleDispatchAllowed()) {
            if (true === in_array($form->getConfiguration()->getIdentifier(), $submittedForms)) {
                if (false === $this->isXhrRequest) {
                    $this->redirect('render');
                } else {
                    $this->response->setStatus(403);

                    return json_encode([
                        'errors' => [
                            'form' => [
                                LocalizationUtility::translate(
                                    'validation.already_submitted',
                                    SettingsUtility::EXTENSION_KEY
                                ),
                            ],
                        ],
                    ]);
                }
            }

            $submittedForms[] = $form->getConfiguration()->getIdentifier();
            $this->sessionService->setKey(self::SESSION_KEY_SUBMITTED_FORMS, $submittedForms);
        }

        $this->view->setTemplatePathAndFilename($form->getConfiguration()->getSubmitTemplate());

        foreach ($form->getConfiguration()->getFinisher() as $finisher) {
            try {
                $finisher->process($form);
            } catch (FinisherException $exception) {
                if (false === $form->getConfiguration()->isMultipleDispatchAllowed()) {
                    unset($submittedForms[array_search($form->getConfiguration()->getIdentifier(), $submittedForms)]);
                    $this->sessionService->setKey(self::SESSION_KEY_SUBMITTED_FORMS, $submittedForms);
                }

                $this->response->setStatus(500);

                if (true === $this->isXhrRequest) {
                    return json_encode(['errors' => ['form' => [$exception->getMessage()]]]);
                } else {
                    $this->forwardWithException($exception);
                }
            }
        }

        $this->view->assign('form', $form);

        if (true === $this->isXhrRequest) {
            return json_encode(['html' => $this->view->render()]);
        }

        return $this->view->render();
    }

    /**
     * @return string
     */
    public function errorAction(): string
    {
        $this->response->setStatus(422);

        if (false === $this->isXhrRequest) {
            return parent::errorAction();
        }

        $response = ['errors' => ['field' => []]];

        $validationResults = $this->arguments->validate()->forProperty('form')->getFlattenedErrors();

        /** @var \TYPO3\CMS\Extbase\Error\Error[] $fieldErrors */
        foreach ($validationResults as $fieldName => $fieldErrors) {
            $response['errors']['field'][$fieldName] = [];

            foreach ($fieldErrors as $error) {
                $response['errors']['field'][$fieldName][] = $error->getMessage();
            }
        }

        return json_encode($response);
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Controller\Exception\RequiredArgumentMissingException
     */
    protected function mapRequestArgumentsToControllerArguments(): void
    {
        try {
            parent::mapRequestArgumentsToControllerArguments();
        } catch (PropertyException $exception) {
            if ($exception->getPrevious() instanceof TypeConverterException) {
                $this->forwardWithException($exception->getPrevious());
            }
        }
    }

    /**
     * @param \Exception $exception
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    protected function forwardWithException(\Exception $exception)
    {
        self::$previousError = $exception->getPrevious();
        $this->clearCacheOnError();
        $this->forwardToReferringRequest();
    }
}
