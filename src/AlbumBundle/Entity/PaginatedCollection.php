<?php


namespace AlbumBundle\Entity;

/**
 * Class PaginatedCollection
 * @package AlbumBundle\Controller
 */
class PaginatedCollection
{
    private $results;
    private $total;
    private $count;
    private $links = array();

    /**
     * PaginatedCollection constructor.
     *
     * @param $results
     * @param $total
     */
    public function __construct($results, $total)
    {
        $this->results = $results;
        $this->total = $total;
        $this->count = count($results);
    }

    public function addLink($ref, $url)
    {
        $this->links[$ref] = $url;
    }
}