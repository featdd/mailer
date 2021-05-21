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
use Featdd\Mailer\Mail\FluidEmail;
use Featdd\Mailer\Utility\ConfigurationUtility;
use Featdd\Mailer\Utility\ContentElementMailerDataUtility;
use Featdd\Mailer\Utility\PathUtility;
use Featdd\Mailer\Utility\SettingsUtility;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail as CoreFluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Featdd\Mailer\Finisher
 */
class EmailFinisher extends AbstractFinisher
{
    /**
     * @return string
     */
    public static function tcaPaletteLabel(): string
    {
        return 'LLL:EXT:' . SettingsUtility::EXTENSION_KEY . '/Resources/Private/Language/locallang.xlf:finisher.email.palette_label';
    }

    /**
     * @return string[]|null
     */
    public static function tcaPaletteShowItem(): ?array
    {
        return ['mailer_receiver_name', 'mailer_receiver_email'];
    }

    /**
     * @return array
     */
    public static function tcaColumns(): array
    {
        return [
            'mailer_receiver_name' => [
                'exclude' => true,
                'label' => 'LLL:EXT:' . SettingsUtility::EXTENSION_KEY . '/Resources/Private/Language/locallang.xlf:finisher.email.receiver_name',
                'config' => [
                    'type' => 'input',
                    'default' => null,
                    'placeholder' => '{toName}',
                    'mode' => 'useOrOverridePlaceholder',
                    'size' => 30,
                    'eval' => 'trim,null',
                ],
            ],
            'mailer_receiver_email' => [
                'exclude' => true,
                'label' => 'LLL:EXT:' . SettingsUtility::EXTENSION_KEY . '/Resources/Private/Language/locallang.xlf:finisher.email.receiver_email',
                'config' => [
                    'type' => 'input',
                    'default' => null,
                    'placeholder' => '{toEmail}',
                    'mode' => 'useOrOverridePlaceholder',
                    'size' => 30,
                    'eval' => 'trim,email,null',
                ],
            ],
        ];
    }

    /**
     * @return array|null
     */
    public static function sqlColumns(): ?array
    {
        return [
            'mailer_receiver_name varchar(255) DEFAULT \'\'',
            'mailer_receiver_email varchar(255) DEFAULT \'\'',
        ];
    }

    /**
     * @param \Featdd\Mailer\Domain\Model\Form $form
     * @throws \Featdd\Mailer\Configuration\Exception
     * @throws \Featdd\Mailer\Finisher\Exception
     */
    public function process(Form $form): void
    {
        ConfigurationUtility::processFinisherOptionsFormVariables($this->options, $form);
        $contentElementData = ContentElementMailerDataUtility::contentElementData($form->getContentElementUid());

        if (false === empty($contentElementData['mailer_receiver_name'])) {
            $this->options['toName'] = $contentElementData['mailer_receiver_name'];
        }

        if (false === empty($contentElementData['mailer_receiver_email'])) {
            $this->options['toEmail'] = $contentElementData['mailer_receiver_email'];
        }

        if (
            true === empty($this->options['toEmail']) ||
            false === GeneralUtility::validEmail($this->options['toEmail'])
        ) {
            throw new ConfigurationException('Missing or invalid "toEmail" option for email finisher');
        }

        if (true === empty($this->options['subject'])) {
            throw new ConfigurationException('Missing "subject" option for email finisher');
        }

        if (true === empty($this->options['template'])) {
            throw new ConfigurationException('Missing template for email finisher');
        }

        $template = PathUtility::resolveAbsoluteTemplatePath($this->options['template']);
        $format = CoreFluidEmail::FORMAT_BOTH;
        $filePath = pathinfo($template, PATHINFO_DIRNAME) . '/' . pathinfo($template, PATHINFO_FILENAME);

        switch (pathinfo($template, PATHINFO_EXTENSION)) {
            case 'html':
                if (false === file_exists($filePath . '.txt')) {
                    $format = CoreFluidEmail::FORMAT_HTML;
                }
                break;
            case 'txt':
                if (false === file_exists($filePath . '.html')) {
                    $format = CoreFluidEmail::FORMAT_PLAIN;
                } else {
                    $template = $filePath . '.html';
                }
                break;
            default:
                throw new ConfigurationException('Invalid template file type "' . pathinfo($template, PATHINFO_BASENAME) . '" for email finisher');
        }

        $email = GeneralUtility::makeInstance(FluidEmail::class)
            ->to(new Address($this->options['toEmail'], $this->options['toName'] ?? ''))
            ->from(new Address($this->options['fromEmail'], $this->options['fromName'] ?? ''))
            ->subject($this->options['subject'])
            ->format($format)
            ->setTemplatePath($template)
            ->assign('form', $form);

        // TODO: handle file attachments

        try {
            /** @var \TYPO3\CMS\Core\Mail\Mailer $mailer */
            $mailer = GeneralUtility::makeInstance(Mailer::class);
            $mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
