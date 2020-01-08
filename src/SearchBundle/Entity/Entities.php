<?php

namespace SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entities class.
 *
 * @ORM\Table(name="`entities`")
 * @ORM\Entity(repositoryClass="SearchBundle\Repository\EntitiesRepository")
 *
 * @package SearchBundle\Entity
 */
class Entities
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
     * @ORM\Column(name="entityField", type="string", length=255, nullable=false)
     */
    private $entityField;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param string $entityName
     *
     * @return Entities
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityField()
    {
        return $this->entityField;
    }

    /**
     * @param string $entityField
     *
     * @return Entities
     */
    public function setEntityField($entityField)
    {
        $this->entityField = $entityField;
        return $this;
    }
}
