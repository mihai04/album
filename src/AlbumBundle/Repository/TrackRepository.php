<?php


namespace AlbumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query as QueryAlias;

/**
 * Class TrackRepository
 * @package AlbumBundle\Repository
 */
class TrackRepository extends EntityRepository
{
    /**
     * Return tracks by ID.
     *
     * @param $albumID
     *
     * @return QueryAlias
     */
    public function getTracksByAlbumID($albumID) {

        $qb = $this->createQueryBuilder('track');
        $qb->where('track.album = :albumID')
            ->setParameter(':albumID', $albumID);

        return $qb->getQuery();
    }

    /**
     * Return all the albums (Track Entity)
     *
     * @return QueryAlias
     */
    public function getTracks() {
        $queryBuilder = $this->createQueryBuilder('track');

        return $queryBuilder->getQuery();
    }
}