<?php
declare(strict_types=1);

namespace Featdd\Mailer\Service;

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

use Featdd\Mailer\Utility\SettingsUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @package Featdd\Mailer\Service
 */
class SessionService implements SingletonInterface
{
    public const SESSION_KEY_PREFIX = SettingsUtility::EXTENSION_KEY . '_';

    /**
     * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    protected FrontendUserAuthentication $frontendUserAuthentication;

    /**
     * @throws \Featdd\Mailer\Service\Exception
     */
    public function __construct()
    {
        if (!$GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
            throw new Exception('Missing TypoScriptFrontendController in $GLOBALS');
        }

        $frontendUserAuthentication = $GLOBALS['TSFE']->fe_user;

        if (!$frontendUserAuthentication instanceof FrontendUserAuthentication) {
            throw new Exception('Failed to instantiate service due to missing FrontendUserAuthentication');
        }

        $this->frontendUserAuthentication = $frontendUserAuthentication;
    }

    /**
     * @param string $key
     * @param mixed $data
     */
    public function setKey(string $key, $data): void
    {
        $this->frontendUserAuthentication->setAndSaveSessionData(self::SESSION_KEY_PREFIX . $key, $data);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getKey(string $key)
    {
        return $this->frontendUserAuthentication->getSessionData(self::SESSION_KEY_PREFIX . $key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasKey(string $key): bool
    {
        return null !== $this->frontendUserAuthentication->getSessionData(self::SESSION_KEY_PREFIX . $key);
    }

    /**
     * @param string $key
     */
    public function deleteKey(string $key): void
    {
        $this->frontendUserAuthentication->setAndSaveSessionData($key, null);
    }
}
