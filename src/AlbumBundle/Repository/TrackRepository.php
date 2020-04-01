<?php


namespace AlbumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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
     * Returns track.
     *
     * @param $albumID
     * @param $trackID
     *
     * @return QueryAlias
     * @throws NonUniqueResultException
     */
    public function getTrack($albumID, $trackID) {

        $qb = $this->createQueryBuilder('track');


        $qb->where('track.id = :trackID')
            ->andWhere('track.album = :albumID')
            ->setParameter(':albumID', $albumID)
            ->setParameter(':trackID', $trackID);

        return $qb->getQuery()->getOneOrNullResult();
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