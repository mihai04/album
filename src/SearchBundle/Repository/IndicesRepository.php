<?php


namespace SearchBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use SearchBundle\Entity\Indexes;
use SearchBundle\Helper\IndexesBuilder;
use UserBundle\Entity\User;
use UserBundle\UserBundle;

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
            ->createQueryBuilder('sqb');

        $terms = $queryBuilder
            ->select('sqb.'.$field.', sqb.id')
            ->getQuery()
            ->getResult();


        foreach ($terms as $term) {
//            $this->setEntity($entity)
//                ->setSearchTerm($term[$field])
//                ->setForeignKey($term['id']);

            $indexesBuilder = new IndexesBuilder();
            $searchIndex= $indexesBuilder->withEntityName($entity)
                ->withSearchTerm($term[$field])
                ->withForeignKey($term['id'])
                ->build();

//            $searchIndex = new Indexes();
//            $searchIndex->setEntity($entity);
//            $searchIndex->setSearchTerm($term[$field]);
//            $searchIndex->setForeignKey($term['id']);

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
        $queryBuilder = $this->createQueryBuilder('search_index');
        $queryBuilder
            ->where('search_index.searchTerm LIKE :searchTerm')
            ->setParameter(':searchTerm', '%'.$searchTerm.'%');

        return $queryBuilder->getQuery()->getResult();
    }
}