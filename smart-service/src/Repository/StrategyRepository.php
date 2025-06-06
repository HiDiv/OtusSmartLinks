<?php

namespace App\Repository;

use App\Entity\Strategy;
use App\Services\StrategyFetcherInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StrategyRepository extends ServiceEntityRepository implements StrategyFetcherInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Strategy::class);
    }

    public function fetchForPath(string $path): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.conditions', 'c')->addSelect('c')
            ->leftJoin('s.action', 'a')->addSelect('a')
            ->where('s.path = :path')
            ->setParameter('path', $path)
            ->orderBy('s.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
