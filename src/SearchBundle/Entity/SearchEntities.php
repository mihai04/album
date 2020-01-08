<?php

namespace SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SearchEntities
 *
 * @ORM\Table(name="`search_entities`")
 * @ORM\Entity(repositoryClass="SearchBundle\Repository\SearchEntitiesRepository")
 */
class SearchEntities
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
     * @ORM\Column(name="entityName", type="string", length=255, nullable=false)
     */
    private $entityName;

    /**
     * @var string
     *
     * @ORM\Column(name="field", type="string", length=255, nullable=false)
     */
    private $field;


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
     * @param string $entityName
     *
     * @return SearchEntities
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Get entityName.
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Set field.
     *
     * @param string $field
     *
     * @return SearchEntities
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
}
