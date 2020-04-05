<?php


namespace AlbumBundle\Controller;


use AlbumBundle\Entity\FestivalsLocation;
use AlbumBundle\Service\SkiddleService;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\CalendarChart;
use DateTime;
use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class SkiddleController
 *
 * @package AlbumBundle\Controller
 */
class SkiddleController extends Controller
{
    /** @var SkiddleService $skiddleService */
    private $skiddleService;

    /** @var string */
    private $apiKey;

    public function __construct(SkiddleService $skiddleService, $apiKey)
    {
        $this->skiddleService = $skiddleService;
        $this->apiKey = $apiKey;
    }

    /**
     * @param Request $request
     *
     * @return ResponseAlias
     */
    public function viewFestivals(Request $request)
    {
        try {

            $festivalsLocation = $this->getfestivalDetails();
            $skiddlefestivals = $this->skiddleService->consumeEvents($festivalsLocation, $this->apiKey);

            if (array_key_exists('results', $skiddlefestivals)) {

                $festivals = [];
                $festival = [];

                foreach ($skiddlefestivals['results'] as $skiddleFestival) {

                    if (array_key_exists('id', $skiddleFestival)) {
                        $festival['id'] = $skiddleFestival['id'];
                    }

                    if (array_key_exists('eventname', $skiddleFestival)) {
                        $festival['name'] = $skiddleFestival['eventname'];
                    }

                    if (array_key_exists('cancellationReason', $skiddleFestival)) {
                        $festival['cancellationReason'] = $skiddleFestival['cancellationReason'];
                    }

                    $venue = [];
                    if (array_key_exists('venue', $skiddleFestival)) {

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

                    if (array_key_exists('link', $skiddleFestival)) {
                        $festival['link'] = $skiddleFestival['link'];
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

                    if (array_key_exists('goingtocount', $skiddleFestival)) {
                        $festival['goingtocount'] = $skiddleFestival['goingtocount'];
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

                    if (!empty($festivalCalendarEntry)) {
                        $festivalCalendars[] = $festivalCalendarEntry;
                    }
                }

                /** @var Paginator $paginator */
                $paginator = $this->get('knp_paginator');
                $paginatedFestivals = $paginator->paginate(
                    $festivals,
                    $request->query->getInt('page', 1), $this->getParameter('page_limit_festivals')
                );

                return $this->render('@Album/Default/skiddleFestivals.html.twig', ['festivals' =>
                    $paginatedFestivals, 'error' => '']);
            }

            return $this->render('@Album/Default/skiddleFestivals.html.twig', ['festivals' =>
                null, 'error' => 'No Results!']);

        } catch (\Exception $e) {
            return $this->render('@Album/Default/skiddleFestivals.html.twig', ['festivals' =>
                null, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @return FestivalsLocation
     */
    private function getFestivalDetails()
    {
        return new FestivalsLocation($this->getParameter('skiddle_limit'),
            $this->getParameter('skiddle_event'));
    }

    /**
     * Retrieves festival and builds a calendar entry.
     *
     * @return ResponseAlias
     */
    public function viewFestivalsCalender()
    {
        try {

            $festivalsLocation = $this->getfestivalDetails();
            $skiddlefestivals = $this->skiddleService->consumeEvents($festivalsLocation, $this->apiKey);

            if (array_key_exists('results', $skiddlefestivals)) {
                $calendarData = [];
                $calendarEntry = [];

                foreach ($skiddlefestivals['results'] as $skiddleFestival) {

                    if (array_key_exists('date', $skiddleFestival)) {
                        $calendarEntry[0] = new DateTime($skiddleFestival['date']);
                    }

                    if (array_key_exists('goingtocount', $skiddleFestival)) {
                        $calendarEntry[1] = (int)$skiddleFestival['goingtocount'];
                    }

                    if (!empty($calendarEntry)) {
                        $calendarData[] = $calendarEntry;
                    }
                }

                $calendar = new CalendarChart();

                $calendarArray = array([['label' => 'Date', 'type' => 'date'], ['label' => 'Attendance', 'type' => 'number']]);
                foreach ($calendarData as $entry) {
                    array_push($calendarArray, $entry);
                }

                $calendar->getData()->setArrayToDataTable($calendarArray);
                $calendar->getOptions()->setTitle('Upcoming music festivals dates')->setHeight(24);
                $calendar->getOptions()->setHeight(350);
                $calendar->getOptions()->setWidth(1200);
                $calendar->getOptions()->getCalendar()->setCellSize(20);
                $calendar->getOptions()->getCalendar()->getCellColor()->setStroke('#76a7fa');
                $calendar->getOptions()->getCalendar()->getCellColor()->setStrokeOpacity(0.5);
                $calendar->getOptions()->getCalendar()->getCellColor()->setStrokeWidth(1);
                $calendar->getOptions()->getCalendar()->getFocusedCellColor()->setStroke('#d3362d');
                $calendar->getOptions()->getCalendar()->getFocusedCellColor()->setStrokeOpacity(1);
                $calendar->getOptions()->getCalendar()->getFocusedCellColor()->setStrokeWidth(1);
                $calendar->getOptions()->getCalendar()->getDayOfWeekLabel()->setFontName('Times-Roman');
                $calendar->getOptions()->getCalendar()->getDayOfWeekLabel()->setFontSize(14);
                $calendar->getOptions()->getCalendar()->getDayOfWeekLabel()->setColor('#1a8763');
                $calendar->getOptions()->getCalendar()->getDayOfWeekLabel()->setItalic(true);
                $calendar->getOptions()->getCalendar()->getDayOfWeekLabel()->setBold(true);
                $calendar->getOptions()->getCalendar()->setDayOfWeekRightSpace(25);
                $calendar->getOptions()->getCalendar()->setDaysOfWeek('SMTWTVS');
                $calendar->getOptions()->getCalendar()->getMonthLabel()->setFontName('Times-Roman');
                $calendar->getOptions()->getCalendar()->getMonthLabel()->setFontSize(12);
                $calendar->getOptions()->getCalendar()->getMonthLabel()->setBold(true);
                $calendar->getOptions()->getCalendar()->getMonthLabel()->setItalic(true);
                $calendar->getOptions()->getCalendar()->getMonthLabel()->setColor('blue');
                $calendar->getOptions()->getCalendar()->getMonthOutlineColor()->setStroke('blue');
                $calendar->getOptions()->getCalendar()->getMonthOutlineColor()->setStrokeOpacity(0.8);
                $calendar->getOptions()->getCalendar()->getMonthOutlineColor()->setStrokeWidth(2);
                $calendar->getOptions()->getCalendar()->getUnusedMonthOutlineColor()->setStroke('#bc5679');
                $calendar->getOptions()->getCalendar()->getUnusedMonthOutlineColor()->setStrokeOpacity(0.8);
                $calendar->getOptions()->getCalendar()->getUnusedMonthOutlineColor()->setStrokeWidth(1);
                $calendar->getOptions()->getCalendar()->setUnderMonthSpace(16);
                $calendar->getOptions()->getCalendar()->setUnderYearSpace(10);
                $calendar->getOptions()->getCalendar()->getYearLabel()->setFontName('Times-Roman');
                $calendar->getOptions()->getCalendar()->getYearLabel()->setFontSize(32);
                $calendar->getOptions()->getCalendar()->getYearLabel()->setColor('#1A8763');
                $calendar->getOptions()->getCalendar()->getYearLabel()->setBold(true);
                $calendar->getOptions()->getCalendar()->getYearLabel()->setItalic(true);

                return $this->render('@Album/Default/skiddleFestivalsCalendar.html.twig', ['calendar' => $calendar, 'error' => '']);
            }

        } catch (\Exception $e) {
            return $this->render('@Album/Default/skiddleFestivalsCalendar.html.twig', ['calendar' => null,
                'error' => $e->getMessage()]);
        }
    }
}