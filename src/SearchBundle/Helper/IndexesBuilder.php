<?php


namespace SearchBundle\Helper;

use SearchBundle\Entity\Indexes;

class IndexesBuilder implements Builder {

    private $indexes;

    public function __construct()
    {
        $this->indexes = new Indexes();
    }

    public function withEntityName($entityName)
    {
        $this->indexes->setEntity($entityName);

        return $this;
    }

    public function withSearchTerm($searchTerm)
    {
        $this->indexes->setSearchTerm($searchTerm);

        return $this;
    }

    public function withForeignKey($foreignKey)
    {
        $this->indexes->setForeignKey($foreignKey);

        return $this;
    }

    public function build()
    {
        return $this->indexes;
    }
}