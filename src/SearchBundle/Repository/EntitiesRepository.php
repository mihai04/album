<?php


namespace SearchBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class EntitiesRepository
 *
 * @package Repository
 */
class EntitiesRepository extends EntityRepository
{
    /**
     * @return mixed
     */
    public function getEntities()
    {
        $queryBuilder = $this->createQueryBuilder('e');

        return $queryBuilder->getQuery()->getResult();
    }
}