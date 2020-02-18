<?php


namespace SearchBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use SearchBundle\Entity\Indices;

/**
 * Class SearchIndexRepository
 * @package Repository
 */
class SearchIndexRepository extends EntityRepository
{
    /**
     * @param $entity
     * @param $field
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function generateIndex($entity, $field)
    {
        $em = $this->getEntityManager();

        $queryBuilder = $em
            ->getRepository($entity)
            ->createQueryBuilder('sqb');

        $terms = $queryBuilder
            ->select('sqb.'.$field.',sqb.id')
            ->getQuery()
            ->getResult();

        foreach ($terms as $term) {
            $searchIndex = new Indices();
            $searchIndex->setEntity($entity);
            $searchIndex->setSearchTerm($term[$field]);
            $searchIndex->setForeignKey($term['id']);

            $em->persist($searchIndex);
            $em->flush();
        }
    }


    /**
     * @param string $searchTerm
     *
     * @return array|null
     */
    public function getSearchResults($searchTerm)
    {
        $queryBuilder = $this->createQueryBuilder('search_index');
        $queryBuilder
            ->where('search_index.searchTerm LIKE :searchTerm')
            ->setParameter(':searchTerm', '%'.$searchTerm.'%');

        return $queryBuilder->getQuery()->getResult();
    }
}