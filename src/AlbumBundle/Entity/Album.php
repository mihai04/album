<?php


namespace AlbumBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ReviewBundle\Entity\Review;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\NotBlank()
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="artist", type="string", length=255, nullable=false)
     */
    private $artist;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="isrc", type="string", length=12, unique=true, nullable=false)
     */
    private $isrc;

    /**
     * @var array
     *
     * @ORM\Column(name="trackList", type="array")
     */
    private $trackList;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Image()
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_published", type="boolean")
     */
    private $isPublished = true;

    /**
     * @var ArrayCollection
     *
     * This is the inverse side of the relationship.
     *
     * @ORM\OneToMany(targetEntity="ReviewBundle\Entity\Review", mappedBy="album")
     * @ORM\OrderBy({"timestamp"="DESC"})
     */
    private $entries;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * @param string $artist
     */
    public function setArtist($artist)
    {
        $this->artist = $artist;
    }

    /**
     * @return string
     */
    public function getIsrc()
    {
        return $this->isrc;
    }

    /**
     * @param string $isrc
     */
    public function setIsrc($isrc)
    {
        $this->isrc = $isrc;
    }

    /**
     * @return array
     */
    public function getTrackList()
    {
        return $this->trackList;
    }

    /**
     * @param array $trackList
     */
    public function setTrackList($trackList)
    {
        $this->trackList = $trackList;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->isPublished;
    }

    /**
     * @param bool $isPublished
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;
    }

    /**
     * @return ArrayCollection|Review[] added for methods autocompletion.
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Given the relationship, one cannot set data on the inverse side (it is only for reading).
     * It can only be set on the owning side. Put in different terms, Doctrine will ignore it.
     *
     * @param ArrayCollection $entries
     */
    public function setEntries($entries)
    {
        $this->entries = $entries;
    }
}