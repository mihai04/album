<?php


namespace AlbumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;


/**
 * In order to isolate, reuse and test Review queries, it is a good practice to create a custom repository class for
 * the Review entity.
 *
 * Class ReviewRepository
 * @package AlbumBundle\Repository
 */
class ReviewRepository extends EntityRepository
{
    /**
     * Returns all the review for a given albumID.
     *
     * @param $albumID
     *
     * @return Query
     */
    public function getReviewsByAlbumID($albumID) {

        $qb = $this->createQueryBuilder('review');
        $qb->where('review.album = :albumID')
            ->orderBy('review.timestamp', 'DESC')
            ->setParameter(':albumID', $albumID);

        return $qb->getQuery();
    }

    /**
     * Gets all reviews for the given User ID.
     *
     * @param $reviewID
     * @param $userID
     *
     * @return Query
     * @throws NonUniqueResultException
     */
    public function getReview($userID, $reviewID)
    {
        $qb = $this->createQueryBuilder('review');
        $qb->where('review.id = :reviewID')
            ->andWhere('review.reviewer = :userID')
            ->setParameter(':userID', $userID)
            ->setParameter(':reviewID', $reviewID);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Returns review.
     *
     * @param $albumID
     * @param $reviewID
     *
     * @return Query
     * @throws NonUniqueResultException
     */
    public function getReviewByAlbum($albumID, $reviewID)
    {
        $qb = $this->createQueryBuilder('review');
        $qb->where('review.id = :reviewID')
            ->andWhere('review.album = :albumID')
            ->setParameter(':albumID', $albumID)
            ->setParameter(':reviewID', $reviewID);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Gets all reviews for the given User ID.
     *
     * @param $userID
     *
     * @return Query
     */
    public function getReviewsByUser($userID)
    {
        $queryBuilder = $this->createQueryBuilder('review');
        $queryBuilder
            ->where('review.reviewer = :userID')
            ->orderBy('review.timestamp', 'DESC')
            ->setParameter(':userID', $userID);

        return $queryBuilder->getQuery();
    }

    public function findAllQueryBuilder()
    {
        return $this->createQueryBuilder('album');
    }
}