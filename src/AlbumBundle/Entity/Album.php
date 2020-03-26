<?php


namespace AlbumBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

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
     *
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="artist", type="string", length=255, nullable=false)
     */
    private $artist;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @Assert\Length(max="15", maxMessage="Invalid ISRC - it requieres maximum 15 characters.")
     * @ORM\Column(name="isrc", type="string", length=50, unique=true, nullable=false)
     */
    private $isrc;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="summary", type="text", nullable=true)
     */
    private $summary;

    /**
     * @var bool
     *
     * @Serializer\Exclude()
     *
     * @ORM\Column(name="is_published", type="boolean")
     */
    private $isPublished = true;

    /**
     * @var |DateTime|null
     *
     * @ORM\Column(name="timestamp", type="datetime", nullable=true)
     */
    private $timestamp;

    /**
     * @var string
     *
     * @ORM\Column(name="published", type="string", length=255, nullable=true)
     */
    private $published;

    /**
     * @var string
     *
     * @ORM\Column(name="listeners", type="string", length=255, nullable=true)
     */
    private $listeners;

    /**
     * @var string
     *
     * @ORM\Column(name="playcount", type="string", length=255, nullable=true)
     */
    private $playcount;

    /**
     * @var string
     *
     * @ORM\Column(name="tags", type="string", nullable=true)
     */
    private $tags;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", nullable=true)
     */
    private $url;

    /**
     * @var ArrayCollection
     *
     * @Serializer\Exclude()
     *
     * This is the inverse side of the relationship.
     *
     * @ORM\OneToMany(targetEntity="AlbumBundle\Entity\Review", mappedBy="album", cascade={"remove"})
     * @ORM\OrderBy({"timestamp"="DESC"})
     */
    private $reviews;

    /**
     * @var ArrayCollection $albumTracks
     *
     * @Serializer\Exclude()
     *
     * @ORM\OneToMany(targetEntity="AlbumBundle\Entity\Track",
     *     mappedBy="album",
     *     fetch="EXTRA_LAZY",
     *     orphanRemoval=true,
     *     cascade={"persist"})
     *
     * @ORM\JoinColumn(name="album", referencedColumnName="id", nullable=false)
     */
    private $albumTracks;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->albumTracks = new ArrayCollection();
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
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
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
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * Given the relationship, one cannot set data on the inverse side (it is only for reading).
     * It can only be set on the owning side. Put in different terms, Doctrine will ignore it.
     *
     * @param ArrayCollection $reviews
     */
    public function setReviews($reviews)
    {
        $this->reviews = $reviews;
    }

    /**
     * @return ArrayCollection
     */
    public function getAlbumTracks()
    {
        return $this->albumTracks;
    }

    /**
     *
     * @param ArrayCollection $albumTracks
     */
    public function setAlbumTracks($albumTracks)
    {
        $this->albumTracks = $albumTracks;
    }

    /**
     * @param Track $track
     * @return $this
     */
    public function addAlbumTracks(Track $track)
    {
        if ($this->albumTracks->contains($track)) {
            return;
        }

        $this->albumTracks[] = $track;
        $track->setAlbum($this);

        return $this;
    }

    /**
     * @param Track $track
     */
    public function removeAlbumTrack(Track $track)
    {
        if ($this->albumTracks->contains($track)) {
            return;
        }

        $this->albumTracks->remove($track);
        // no need to persist, keep in sync
        $track->setAlbum(null);
    }

    /**
     * @return DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param |DateTime $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @param string $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * @return string
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * @param string $listeners
     */
    public function setListeners($listeners)
    {
        $this->listeners = $listeners;
    }

    /**
     * @return string
     */
    public function getPlaycount()
    {
        return $this->playcount;
    }

    /**
     * @param string $playcount
     */
    public function setPlaycount($playcount)
    {
        $this->playcount = $playcount;
    }

    /**
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}