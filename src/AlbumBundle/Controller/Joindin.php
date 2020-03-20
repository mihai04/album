<?php


namespace AlbumBundle\Controller;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Joindin extends Controller
{
    // get data into out database

    private function eventsAction($url, array $options) {

        $client = new Client(['base_uri' => 'https://api.joind.in',
            'timeout' => 2.0,
            'default' => [
                'exceptions' => false
                ]
        ]);

        $options = array_merge([ 'filter' => 'past' ], $options);

        $response = $client->request('GET', 'v2.1/' . $url, $options);

        $events = json_decode($response->getBody(), true);

        $event = $events['events'][1];
        $talksData = $client->request('GET', 'v2.1/' . $url, $event['talks_uri']);

        dump($talksData);die;


//        dump($response->getStatusCode(), $response, $events['events']);die;

//        $event = array_values($events['events'])[0];
//        dump($response->getStatusCode(), $response, $event);die;
//
////        $talksData = $client->request('GET', 'v2.1/' . $url, $talkOptions[0]['talks_uri']);

//        dump($response->getStatusCode(), $response, $data);die;

        return $this->render('AlbumBundle:Default:event.html.twig', ['data' => $events]);
    }

    public function eventAction() {
        $options =
            ['query' => [
                'title' => 'java' ]
            ]
        ;

        return $this->eventsAction('events', $options);
    }

    public function talksAction() {
        $options = [
            [ 'filter' => 'past']
        ];

        return $this->eventsAction('talks', $options);
    }
}