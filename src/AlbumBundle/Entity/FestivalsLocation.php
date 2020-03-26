<?php


namespace AlbumBundle\Entity;

/**
 * Class UKFestivalsLocation
 *
 * @package AlbumBundle\Entity
 */
class FestivalsLocation
{
    private $limit;
    private $event;

    /**
     * UKFestivalsLocation constructor.
     *
     * @param integer $limit
     * @param string $event
     */
    public function __construct($limit, $event)
    {
        $this->limit = $limit;
        $this->event = $event;
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