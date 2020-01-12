<?php


namespace AlbumBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ReviewBundle\Entity\Review;
use Symfony\Component\Validator\Constraints as Assert;
use TrackBundle\Entity\Track;

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
     * @Assert\Length(max="15", maxMessage="Invalid ISRC - it requieres maximum 15 characters.")
     * @ORM\Column(name="isrc", type="string", length=15, unique=true, nullable=false)
     */
    private $isrc;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Image(maxSize="2M")
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="summary", type="text", nullable=true)
     */
    private $summary;

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
    private $reviews;

    /**
     * @var ArrayCollection $albumTracks
     *
     * @ORM\OneToMany(targetEntity="TrackBundle\Entity\Track",
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
}