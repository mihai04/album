<?php


namespace ReviewBundle\Entity;


use AlbumBundle\Entity\Album;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Entity\User;

/**
 * Review
 *
 * @ORM\Table(name="`review`")
 * @ORM\Entity(repositoryClass="ReviewBundle\Repository\ReviewRepository")
 */
class Review
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     * @Assert\NotBlank()
     *
     * @Assert\Length(min = 10,
     *      max = 255,
     *      minMessage = "You must insert at least 10 words",
     *      maxMessage = "You cannot add a text longs than 255 words")
     * @ORM\Column(name="review", type="string", type="text", nullable=false)
     */
    private $review;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Range(min=0, max=5)
     *
     * @ORM\Column(name="rating", type="string")
     */
    private $rating;

    /**
     * CHANGE HERE
     *
     * @var DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime")
     */
    private $timestamp;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="reviews")
     * @ORM\JoinColumn(name="reviewer", referencedColumnName="id", nullable=false)
     */
    private $reviewer;

    /**
     * @var Album
     *
     * This is the owning side of the relationship.
     *
     * @ORM\ManyToOne(targetEntity="AlbumBundle\Entity\Album", inversedBy="entries")
     * @ORM\JoinColumn(name="album", referencedColumnName="id", nullable=false)
     */
    private $album;

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
     * @param $title
     * @return mixed
     */
    public function setTitle($title)
    {
        return $this->title = $title;
    }

    /**
     * @return string
     */
    public function getReview()
    {
        return $this->review;
    }

    /**
     * @param User $review
     */
    public function setReview($review)
    {
        $this->review = $review;
    }

    /**
     * Get rating.
     *
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return DateTime|string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param DateTime $timestamp
     *
     * @return Review
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return User
     */
    public function getReviewer()
    {
        return $this->reviewer;
    }

    /**
     * @param User $reviewer
     */
    public function setReviewer($reviewer)
    {
        $this->reviewer = $reviewer;
    }

    /**
     * @return Album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     *
     * @param Album $album
     */
    public function setAlbum(Album $album)
    {
        $this->album = $album;
    }
}