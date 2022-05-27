<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlotController extends AbstractController
{
    #[Route('/plot', name: 'app_plot')]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findSleepGroupByDate();

        return $this->render('plot/index.html.twig', [
            'events' => $events,
            'avg_day' => 1,
            'avg_night' => 2,
            'avg_awake' => 3
        ]);
    }
}
