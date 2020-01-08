<?php


namespace Helper;


use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MySQLDatabaseHelper
{
    /**
     * @param OutputInterface $output
     * @param EntityManagerInterface $em
     * @param string $table
     * @param bool $isCascade
     */
    public static function truncate(OutputInterface $output, EntityManagerInterface $em, $table, $isCascade)
    {
        try {
            $conn = $em->getConnection();
            $platform = $conn->getDatabasePlatform();
            $conn->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
            $target = $platform->getTruncateTableSQL($table, $isCascade);
            $conn->executeUpdate($target);
            $conn->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');
        }
        catch (DBALException $e) {
            $output->writeln("Error: Failed to update table for the search function.", $e->getMessage());
            exit(0);
        }
    }
}