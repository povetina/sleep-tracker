<?php

namespace App\Service;

use App\Entity\Event;
use App\Repository\EventRepository;

class SleepCountService
{
    public function __construct(
        private EventRepository $eventRepository
    )
    {
    }

    public function getDailyStatistics(): array
    {
        $events = $this->eventRepository->findSleepOrderByDate();

        $summaryByDates = self::buildEventsByDatesArray($events);

        foreach ($events as $event) {
            $started = $event->getStarted();
            $startedStamp = $started->format('Y-m-d');
            $sleepType = $event->getTags()->first()->getName();

            if ($sleepType == 'night' && $event->getStarted()->format('H') > 18) {
                $nextDay = $started->modify('+1 day')->format('Y-m-d');
                $summaryByDates[$nextDay]['night']['duration'] += $event->getDuration();
            } else {
                $summaryByDates[$startedStamp]['day']['duration'] = $this->countDaySleep($event->getStarted());
            }
        }

        foreach ($summaryByDates as $date => $eventsByDate) {
            $dailyEvents = $this->eventRepository->findEventsByDate($date);

            $awakeStart = null;
            $awakeFinish = null;
            foreach ($dailyEvents as $idx => $dailyEvent) {
                if ($dailyEvent->getTags()->first()->getName() == 'night') {
                    if (isset($dailyEvents[$idx + 1]) && $dailyEvents[$idx + 1] instanceof Event) {
                        if ($dailyEvents[$idx + 1]->getTags()->first()->getName() == 'day') {
                            $awakeStart = $dailyEvent->getFinished();
                        }
                    }
                }

                if ($dailyEvent->getTags()->first()->getName() == 'day') {
                    if (isset($dailyEvents[$idx + 1]) && $dailyEvents[$idx + 1] instanceof Event) {
                        if ($dailyEvents[$idx + 1]->getTags()->first()->getName() == 'night') {
                            $awakeFinish = $dailyEvents[$idx + 1]->getStarted();
                        }
                    }
                }
                $summaryByDates[$date]['awake'] = [
                    'started' => $awakeStart,
                    'finished' => $awakeFinish,
                    'day_duration' => 0,
                    'duration' => 0
                ];

                if ($awakeStart instanceof \DateTimeInterface && $awakeFinish instanceof \DateTimeInterface) {
                    $dayDuration = $awakeFinish->getTimestamp() - $awakeStart->getTimestamp();
                    $summaryByDates[$date]['awake']['day_duration'] = $dayDuration;
                    $summaryByDates[$date]['awake']['duration'] = $dayDuration - $summaryByDates[$date]['day']['duration'];
                }
            }
        }
        krsort($summaryByDates);
        return $summaryByDates;
    }

    public function getEventsForPlot(): array
    {
        $events = $this->eventRepository->findSleepOrderByDate();
        $eventsByDates = [];

        foreach ($events as $event) {
            $startedYmd = $event->getStarted()->format('Y-m-d');
            $finishedYmd = $event->getFinished()->format('Y-m-d');
            if ($startedYmd != $finishedYmd) {
                $beforeMidnightEvent = clone $event;
                $afterMidnightEvent = clone $event;

                $beforeMidnightEvent->setFinished(new \DateTimeImmutable(sprintf('%s 23:59:59', $startedYmd)));
                $beforeMidnightEvent->setDuration();

                $afterMidnightEvent->setStarted(new \DateTimeImmutable(sprintf("%s 00:00:00", $finishedYmd)));
                $afterMidnightEvent->setDuration();

                $eventsByDates[$startedYmd][] = $beforeMidnightEvent;
                $eventsByDates[$finishedYmd][] = $afterMidnightEvent;
            } else {
                $eventsByDates[$startedYmd][] = $event;
            }
        }
        krsort($eventsByDates);
        return $eventsByDates;
    }

    /**
     * @param Event[] $events
     * @return array
     */
    public static function buildEventsByDatesArray(array $events): array
    {
        $eventsByDates = [];
        $latestDate = $events[0]->getStarted();
        foreach ($events as $event) {
            if ($event->getStarted() > $latestDate) {
                $latestDate = $event->getStarted();
            }
            $eventsByDates[$event->getStarted()->format('Y-m-d')] = self::buildEventsByDateArray($event->getStarted());
        }

        $nextDate = $latestDate->modify('+1 day');
        $eventsByDates[$nextDate->format('Y-m-d')] = self::buildEventsByDateArray($nextDate);

        return $eventsByDates;
    }

    private static function buildEventsByDateArray(\DateTimeInterface $dateTime): array
    {
        return [
            'night' => [
                'duration' => 0,
                'date' => $dateTime,
                'type' => 'night'
            ],
            'day' => [
                'duration' => 0,
                'date' => $dateTime,
                'type' => 'day'
            ],
            'awake' => [
                'duration' => 0,
                'date' => $dateTime,
            ]
        ];
    }

    public function countDaySleep(\DateTimeInterface $date): ?int
    {
        /** @var Event[] $events */
        $events = $this->eventRepository->findEventsByDate($date->format('Y-m-d'));
        $duration = 0;
        foreach ($events as $event) {
            if ($event->getTagsNames()->contains('day')) {
                $duration += $event->getDuration();
            }
        }

        return $duration;
    }
}