<?php


namespace AlbumBundle\Entity;

/**
 * Class UKFestivalsLocation
 *
 * @package AlbumBundle\Entity
 */
class FestivalsLocation
{
    private $latitude;
    private $longitude;
    private $radius;
    private $limit;
    private $event;

    /**
     * UKFestivalsLocation constructor.
     *
     * @param string $latitude
     * @param string  $longitude
     * @param string $radius
     * @param integer $limit
     * @param string $event
     */
    public function __construct($latitude, $longitude, $radius, $limit, $event)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->radius = $radius;
        $this->limit = $limit;
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @return string
     */
    public function getRadius()
    {
        return $this->radius;
    }

    /**
     * @return integer
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }
}