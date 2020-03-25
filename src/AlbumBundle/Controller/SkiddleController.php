<?php


namespace AlbumBundle\Controller;


use AlbumBundle\Entity\FestivalsLocation;
use AlbumBundle\Service\SkiddleService;
use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SkiddleController extends Controller
{
    /** @var SkiddleService $skiddleService */
    private $skiddleService;

    public function __construct(SkiddleService $skiddleService)
    {
        $this->skiddleService = $skiddleService;
    }

    /**
     * @param Request $request
     *
     * @return ResponseAlias
     */
    public function viewFestivals(Request $request)
    {

        $festivalsLocation = $this->getfestivalDetails();

        $skiddlefestivals = $this->get('skiddle.events')->consumeEvents($festivalsLocation);

        $festivals = [];

        $festival = [];
        foreach ($skiddlefestivals['results'] as $skiddleFestival) {

            if(array_key_exists('id', $skiddleFestival)) {
                $festival['id'] = $skiddleFestival['id'];
            }

            if(array_key_exists('eventname', $skiddleFestival)) {
                $festival['name'] = $skiddleFestival['eventname'];
            }

            $venue = [];
            if(array_key_exists('venue', $skiddleFestival)) {

                if (array_key_exists('id', $skiddleFestival['venue'])) {
                    $venue['id'] = $skiddleFestival['venue']['id'];
                }

                if (array_key_exists('name', $skiddleFestival['venue'])) {
                    $venue['name'] = $skiddleFestival['venue']['name'];
                }

                if (array_key_exists('postcode', $skiddleFestival['venue'])) {
                    $venue['postcode'] = $skiddleFestival['venue']['postcode'];
                }

                if (array_key_exists('country', $skiddleFestival['venue'])) {
                    $venue['country'] = $skiddleFestival['venue']['country'];
                }

                if (array_key_exists('rating', $skiddleFestival['venue'])) {
                    $venue['rating'] = $skiddleFestival['venue']['rating'];
                }

            }

            if (!empty($venue)) {
                $festival['venue'] = $venue;
            }

            if (array_key_exists('largeimageurl', $skiddleFestival)) {
                $festival['largeimageurl'] = $skiddleFestival['largeimageurl'];
            }

            if (array_key_exists('date', $skiddleFestival)) {
                $festival['date'] = $skiddleFestival['date'];
            }

            if (array_key_exists('description', $skiddleFestival)) {
                $festival['description'] = $skiddleFestival['description'];
            }

            if (array_key_exists('minage', $skiddleFestival)) {
                $festival['minage'] = $skiddleFestival['minage'];
            }

            if (array_key_exists('entryprice', $skiddleFestival)) {
                $festival['entryprice'] = $skiddleFestival['entryprice'];
            }

            $artists = [];
            if (array_key_exists('artists', $skiddleFestival)) {

                $artist = [];
                foreach ($skiddleFestival['artists'] as $skiddleartist) {
                    if (array_key_exists('artistid', $skiddleartist)) {
                        $artist['artistid'] = $skiddleartist['artistid'];
                    }

                    if (array_key_exists('name', $skiddleartist)) {
                        $artist['name'] = $skiddleartist['name'];
                    }

                    if (!empty($artist)) {
                        $artists[] = $artist;
                    }
                }
            }

            if (!empty($artists)) {
                $festival['artists'] = $artists;
            }

            if (!empty($festival)) {
                $festivals[] = $festival;
            }
        }

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $paginatedFestivals = $paginator->paginate(
            $festivals,
            $request->query->getInt('page', 1), $this->getParameter('page_limit_festivals')
        );

        return $this->render('@Album/Default/skiddleFestivals.html.twig', ['festivals' => $paginatedFestivals]);
    }

    /**
     * @return FestivalsLocation
     */
    private function getFestivalDetails()
    {
        $this->getParameter('latitude');
        $this->getParameter('longitude');
        $this->getParameter('radius');
        $this->getParameter('limit');
        $this->getParameter('event');

        return new FestivalsLocation($this->getParameter('latitude'), $this->getParameter('longitude'),
            $this->getParameter('radius'), $this->getParameter('limit'), $this->getParameter('event'));
    }
}