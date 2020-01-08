<?php


namespace SearchBundle\Repository;


use Doctrine\ORM\EntityRepository;

/**
 * Class SearchEntitiesRepository
 *
 * @package Repository
 */
class SearchEntitiesRepository extends EntityRepository
{
    public function getSearchEntities()
    {
        $queryBuilder = $this->createQueryBuilder('se');

        return $queryBuilder->getQuery()->getResult();
    }
}