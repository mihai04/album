<?php


namespace AlbumBundle\Controller;

use CMEN\GoogleChartsBundle\GoogleCharts\Charts\Material\BarChart;
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

//        var_dump($events);die;

//        $event = $events['events'][1];
//        $talksData = $client->request('GET', 'v2.1/' . $url, $event['talks_uri']);

//        dump($talksData);die;die


//        dump($response->getStatusCode(), $response, $events['events']);die;

//        $event = array_values($events['events'])[0];
//        dump($response->getStatusCode(), $response, $event);die;
//
////        $talksData = $client->request('GET', 'v2.1/' . $url, $talkOptions[0]['talks_uri']);

//        dump($response->getStatusCode(), $response, $data);die;

        return $this->render('AlbumBundle:Default:event.html.twig', ['data' => $events['events']]);
    }

    public function eventAction() {
//        $options =
//            ['query' => [
//                'title' => 'java' ]
//            ]
//        ;
//
//        return $this->eventsAction('events', $options);

        $bar = new BarChart();
        $bar->getData()->setArrayToDataTable([
            ['City', '2010 Population', '2000 Population'],
            ['New York City, NY', 8175000, 8008000],
            ['Los Angeles, CA', 3792000, 3694000],
            ['Chicago, IL', 2695000, 2896000],
            ['Houston, TX', 2099000, 1953000],
            ['Philadelphia, PA', 1526000, 1517000]
        ]);
        $bar->getOptions()->setTitle('Population of Largest U.S. Cities');
        $bar->getOptions()->getHAxis()->setTitle('Population of Largest U.S. Cities');
        $bar->getOptions()->getHAxis()->setMinValue(0);
        $bar->getOptions()->getVAxis()->setTitle('City');
        $bar->getOptions()->setWidth(900);
        $bar->getOptions()->setHeight(500);

        return $this->render("@Album/Default/event.html.twig", ['bar' => $bar]);

    }

    public function talksAction() {
        $options = [
            [ 'filter' => 'past']
        ];

        return $this->eventsAction('talks', $options);
    }
}