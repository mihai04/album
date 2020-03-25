<?php


namespace AlbumBundle\Service;


use GuzzleHttp\Psr7\Response;

interface APIConsumer
{
    /**
     * @param string $method
     * @param string $uri
     * @param array array $getParams
     *
     * @return Response
     */
    public function consume($method, $uri, array $getParams = []);
}