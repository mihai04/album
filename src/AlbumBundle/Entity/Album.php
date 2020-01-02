<?php


namespace AlbumBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Album
 *
 * @ORM\Table(name="`album`")
 * @ORM\Entity(repositoryClass="AlbumBundle\Repository\AlbumRepository")
 */
class Album
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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="artist", type="string", length=255)
     */
    private $artist;

    /**
     * @var string
     *
     * @ORM\Column(name="isrc", type="string", length=12, unique=true)
     */
    private $isrc;

    /**
     * @var string
     *
     * @ORM\Column(name="image_name", type="string", length=255)
     */
    private $imageName;
}