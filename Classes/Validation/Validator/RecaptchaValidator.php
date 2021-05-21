<?php
declare(strict_types=1);

namespace Featdd\Mailer\Validation\Validator;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * @package Featdd\Mailer\Validation\Validator
 */
class RecaptchaValidator extends AbstractValidator
{
    public const GOOGLE_API_VERIFICATION_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var array[]
     */
    protected $supportedOptions = [
        'secret' => ['', 'The secret used to verify against the reCAPTCHA API', 'string', true],
    ];

    /**
     * @param mixed $value
     */
    protected function isValid($value): void
    {
        $captchaIsValid = $this->requestVerification([
            'secret' => $this->options['secret'] ?? '',
            'response' => $value,
            'remoteip' => GeneralUtility::getIndpEnv('REMOTE_ADDR'),
        ]);

        if (false === $captchaIsValid) {
            $this->addError(
                $this->translateErrorMessage('validation.recaptcha', 'mailer'),
                1602633600
            );
        }
    }

    /**
     * @param array $parameters
     * @return bool
     */
    protected function requestVerification(array $parameters): bool
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, self::GOOGLE_API_VERIFICATION_URL);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);

        $response = json_decode(
            curl_exec($curl),
            true
        );

        curl_close($curl);

        return $response['success'];
    }
}
