<?php


namespace SearchBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use SearchBundle\Helper\IndexesBuilder;

/**
 * Class IndicesRepository
 *
 * @package Repository
 */
class IndicesRepository extends EntityRepository
{
    /**
     * The function create a searchable indices per entity.
     *
     * @param $entity
     * @param $field
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function generateIndex($entity, $field)
    {
        $em = $this->getEntityManager();

        $queryBuilder = $em
            ->getRepository($entity)
            ->createQueryBuilder('q');

        $terms = $queryBuilder
            ->select('q.id, q.'.$field)
            ->getQuery()
            ->getResult();

        foreach ($terms as $term) {
            $indexesBuilder = new IndexesBuilder();
            $searchIndex= $indexesBuilder->withEntityName($entity)
                ->withSearchTerm($term[$field])
                ->withForeignKey($term['id'])
                ->build();

            $em->persist($searchIndex);
            $em->flush();
        }
    }

    /**
     * @param string $searchTerm
     *
     * @return array|null
     */
    public function getResults($searchTerm)
    {
        $queryBuilder = $this->createQueryBuilder('search_indices');
        $queryBuilder
            ->where('search_indices.searchTerm LIKE :searchTerm')
            ->setParameter(':searchTerm', '%'.$searchTerm.'%');

        return $queryBuilder->getQuery()->getResult();
    }
}