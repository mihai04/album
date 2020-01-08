<?php


namespace SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SearchIndex.
 *
 * @ORM\Table(name="`search_index`")
 *
 * @ORM\Entity(repositoryClass="SearchBundle\Repository\SearchIndexRepository")
 */
class SearchIndex
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
    private $foreignId;

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
     * @return SearchIndex
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entityName.
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get foreignId.
     *
     * @return int
     */
    public function getForeignId()
    {
        return $this->foreignId;
    }

    /**
     * Set foreignId.
     *
     * @param int $foreignId
     */
    public function setForeignId($foreignId)
    {
        $this->foreignId = $foreignId;
    }

    /**
     * Set content.
     *
     * @param string $searchTerm
     *
     * @return SearchIndex
     */
    public function setSearchTerm($searchTerm)
    {
        $this->searchTerm = $searchTerm;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }
}
