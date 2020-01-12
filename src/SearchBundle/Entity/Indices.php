<?php


namespace SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Indices.
 *
 * @ORM\Table(name="`indices`")
 *
 * @ORM\Entity(repositoryClass="SearchBundle\Repository\IndicesRepository")
 * @package SearchBundle\Entity
 */
class Indices
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="entity", type="string", nullable=false)
     */
    private $entity;

    /**
     * @var int
     *
     * @ORM\Column(name="foreign_id", type="integer", nullable=false)
     */
    private $foreignKey;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    private $searchTerm;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set entityName.
     *
     * @param string $entity
     *
     * @return Indices
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }
    
    /**
     * @return int
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @param $foreignKey
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;
    }

    /**
     * @param string $searchTerm
     *
     * @return Indices
     */
    public function setSearchTerm($searchTerm)
    {
        $this->searchTerm = $searchTerm;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }
}
