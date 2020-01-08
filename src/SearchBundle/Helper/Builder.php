<?php


namespace SearchBundle\Helper;


use SearchBundle\Entity\Indexes;

interface Builder
{
    public function withEntityName($entity);

    public function withSearchTerm($searchTerm);

    public function withForeignKey($foreignKey);
}