<?php


namespace SearchBundle\Helper;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MySQLDatabaseHelper
 *
 * @package SearchBundle\Helper
 */
class DatabaseHelperImpl implements DatabaseHelper
{
    const QUERY_DISABLE_FK = 'SET FOREIGN_KEY_CHECKS = 0;';
    const QUERY_ENABLE_FK = 'SET FOREIGN_KEY_CHECKS = 1;';

    /**
     * Truncates a given table.
     *
     * @param OutputInterface $output
     * @param EntityManagerInterface $em
     * @param $table
     * @param $isCascade
     * @throws DBALException
     */
    public function truncate(OutputInterface $output, EntityManagerInterface $em, $table, $isCascade)
    {

        $conn = $em->getConnection();
        $platform = $conn->getDatabasePlatform();
        $conn->executeQuery(self::QUERY_DISABLE_FK);

        // generates a Truncate Table SQL
        $target = $platform->getTruncateTableSQL($table, $isCascade);

        $conn->executeUpdate($target);
        $conn->executeQuery(self::QUERY_ENABLE_FK);
    }
}