<?php

namespace TestBundle\Controller;

use Blaga\DateFormatBundle\DateFormat;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @var DateFormat
     */
    private $dateFormat;

    public function __construct(DateFormat $dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    public function indexAction()
    {
        $dateFormat = $this->dateFormat->getDateFormat();
        return $this->render('TestBundle:Default:index.html.twig', [
            'dateFormat' => $dateFormat
        ]);
    }
}
