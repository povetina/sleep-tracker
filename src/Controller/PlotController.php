<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Service\SleepCountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlotController extends AbstractController
{
    #[Route('/plot', name: 'app_plot')]
    public function index(SleepCountService $sleepCountService, EventRepository $eventRepository): Response
    {
        $summary = $sleepCountService->getDailyStatistics();
        /** @var Event[] $events */
        $events = $sleepCountService->getEventsForPlot();

        $avgDay = 0;
        $avgNight = 0;
        $avgAwake = 0;

        $totalDaysDay = count($summary);
        $totalDaysNight = count($summary);
        $totalDaysAwake = count($summary);


        foreach ($summary as $item) {
            $dayDuration = $item['day']['duration'];
            $nightDuration = $item['night']['duration'];
            $awakeDuration = $item['awake']['duration'];

            if ($dayDuration > 0) {
                $avgDay += $item['day']['duration'];
            } else {
                $totalDaysDay--;
            }

            if ($nightDuration > 0) {
                $avgNight += $item['night']['duration'];
            } else {
                $totalDaysNight--;
            }

            if ($awakeDuration > 0) {
                $avgAwake += $item['awake']['duration'];
            } else {
                $totalDaysAwake--;
            }
        }


        return $this->render('plot/index.html.twig', [
            'summary' => $summary,
            'eventsByDates' => $events,
            'avg_day' => round($avgDay / $totalDaysDay, 0),
            'avg_night' => round($avgNight / $totalDaysNight, 0),
            'avg_awake' => round($avgAwake / $totalDaysAwake, 0)
        ]);
    }
}
