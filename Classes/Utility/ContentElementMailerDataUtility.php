<?php
declare(strict_types=1);

namespace Featdd\Mailer\Utility;

use PDO;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
class ContentElementMailerDataUtility
{
    /**
     * @param int $contentElementUid
     * @param string|null $columnName
     * @return mixed
     */
    public static function contentElementData(int $contentElementUid, string $columnName = null)
    {
        $queryBuilder = self::queryBuilder();

        return $queryBuilder
            ->select($columnName ?? '*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($contentElementUid, PDO::PARAM_INT))
            )
            ->execute()
            ->{null !== $columnName ? 'fetchOne' : 'fetchAssociative'}();
    }

    /**
     * @param string $table
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected static function queryBuilder(string $table = 'tt_content'): QueryBuilder
    {
        /** @var \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        return $connectionPool->getQueryBuilderForTable($table);
    }
}
