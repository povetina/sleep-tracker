<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function add(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Event[]
     */
    public function findOrderedByDate(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.started is not null')
            ->orderBy('e.created', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findSleepGroupByDate()
    {
        /** @var Event[] $events */
        $events = $this->createQueryBuilder('e')
            ->andWhere('e.type = :sleep')
            ->setParameter('sleep', 'sleep')
            ->orderBy('e.started', 'DESC')
            ->getQuery()
            ->getResult()
        ;


        $eventsByDates = [];
        foreach ($events as $event) {
            $startedStamp = $event->getStarted()->format('Y-m-d');
            $sleepType = $event->getTags()->first()->getName();
            if (!isset($eventsByDates[$startedStamp])) {
                $eventsByDates[$startedStamp]['night'] = [
                    'duration' => 0,
                    'date' => null,
                    'type' => ''
                ];
                $eventsByDates[$startedStamp]['day'] = [
                    'duration' => 0,
                    'date' => null,
                    'type' => ''
                ];
                $eventsByDates[$startedStamp]['awake'] = [
                    'duration' => 0,
                    'date' => null,
                ];
            }
            $eventsByDates[$startedStamp][$sleepType]['duration'] += $event->getDuration();
            $eventsByDates[$startedStamp][$sleepType]['date'] = $event->getStarted();
            $eventsByDates[$startedStamp][$sleepType]['type'] = $sleepType;
        }

        foreach ($eventsByDates as $date => $eventsByDate) {
            $qb = $this->createQueryBuilder('e');

            /** @var Event[] $dailyEvents */
            $dailyEvents = $qb
                ->andWhere(
                    $qb->expr()->between(
                        'e.started', ':from', ':to'
                    )
                )
                ->orWhere(
                    $qb->expr()->between(
                        'e.finished', ':from', ':to'
                    )
                )
                ->andWhere('e.type = :sleep')
                ->setParameter('sleep', 'sleep')
                ->setParameter('from', sprintf('%s 00:00:00', $date))
                ->setParameter('to', sprintf('%s 23:59:59', $date))
                ->orderBy('e.started', 'ASC')
                ->getQuery()
                ->getResult()
            ;

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
                $eventsByDates[$date]['awake'] = [
                    'started' => $awakeStart,
                    'finished' => $awakeFinish,
                    'day_duration' => 0,
                    'duration' => 0
                ];

                if ($awakeStart instanceof \DateTimeInterface && $awakeFinish instanceof \DateTimeInterface) {
                    $dayDuration = $awakeFinish->getTimestamp() - $awakeStart->getTimestamp();
                    $eventsByDates[$date]['awake']['day_duration'] = $dayDuration;
                    $eventsByDates[$date]['awake']['duration'] = $dayDuration -  $eventsByDates[$date]['day']['duration'];
                }
            }
        }
        return $eventsByDates;
    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
