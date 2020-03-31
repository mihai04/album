<?php


namespace AlbumBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query as QueryAlias;

/**
 * Class UserRepository
 * @package AlbumBundle\Repository
 */
class UserRepository  extends EntityRepository
{
    /**
     * Return all the users (User Entity).
     *
     * @return QueryAlias
     */
    public function findAllQueryBuilder() {
        $queryBuilder = $this->createQueryBuilder('user');

        return $queryBuilder->getQuery();
    }
}