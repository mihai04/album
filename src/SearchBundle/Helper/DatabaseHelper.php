<?php


namespace SearchBundle\Helper;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface DatabaseHelper
{
    /**
     * The Interface can be further implemented by other databases.
     *
     * @param OutputInterface $output
     * @param EntityManagerInterface $em
     * @param $table
     * @param $isCascade
     * @return mixed
     *
     * @throws DBALException
     */
    public function truncate(OutputInterface $output, EntityManagerInterface $em, $table, $isCascade);
}