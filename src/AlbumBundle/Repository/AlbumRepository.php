<?php


namespace AlbumBundle\Repository;

use AlbumBundle\Entity\Album;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query as QueryAlias;

/**
 * In order to isolate, reuse and test Album queries, it is a good practice to create a custom repository class for
 * the AlbumBundle entity.
 *
 * Class AlbumRepository
 * @package AlbumBundle\Repository
 */
class AlbumRepository extends EntityRepository
{
    /**
     * Return all teh albums (Album Entity)
     *
     * @return Album
     */
    public function getAlbums() {
        $queryBuilder = $this->createQueryBuilder('album');
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}