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
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="review", type="review", type="text", nullable=false)
     */
    private $review;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Range(min=0, max=5)
     *
     * @ORM\Column(name="rating", type="string")
     */
    private $rating;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="string")
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
     * @ORM\JoinColumn(name="album", referencedColumnName="id", )
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
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
    public function setReview(User $review)
    {
        $this->review = $review;
    }

    /**
     * @return string
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param string $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param DateTime $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
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