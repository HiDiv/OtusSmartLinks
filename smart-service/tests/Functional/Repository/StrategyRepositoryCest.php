<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Action;
use App\Entity\Condition;
use App\Entity\Strategy;
use App\Repository\StrategyRepository;
use App\Tests\Support\FunctionalTester;
use Doctrine\ORM\EntityManagerInterface;

class StrategyRepositoryCest
{
    public function fetchForPathReturnsEmptyWhenNoStrategies(FunctionalTester $I): void
    {
        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);
        /** @var StrategyRepository $repo */
        $repo = $I->grabService(StrategyRepository::class);

        $path = '/nonexistent';
        $em->createQueryBuilder()
            ->delete(Strategy::class, 's')
            ->where('s.path = :path')
            ->setParameter('path', $path)
            ->getQuery()
            ->execute();
        $em->clear();

        $result = $repo->fetchForPath($path);

        $I->assertIsArray($result);
        $I->assertCount(0, $result);
    }

    public function fetchForPathWithSingleStrategyNoRelations(FunctionalTester $I): void
    {
        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);
        /** @var StrategyRepository $repo */
        $repo = $I->grabService(StrategyRepository::class);

        $path = '/single';
        $em->createQueryBuilder()
            ->delete(Strategy::class, 's')
            ->where('s.path = :path')
            ->setParameter('path', $path)
            ->getQuery()
            ->execute();
        $em->clear();

        $strategy = new Strategy();
        $strategy->setPath($path);
        $strategy->setPriority(10);

        $em->persist($strategy);
        $em->flush();
        $em->clear();

        $results = $repo->fetchForPath($path);

        $I->assertCount(1, $results);
        /** @var Strategy $fetched */
        $fetched = $results[0];
        $I->assertEquals($path, $fetched->getPath());
        $I->assertEquals(10, $fetched->getPriority());
        $I->assertTrue($fetched->getConditions()->isEmpty());
        $I->assertNull($fetched->getAction());
    }

    public function fetchForPathOrdersByPriorityDesc(FunctionalTester $I): void
    {
        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);
        /** @var StrategyRepository $repo */
        $repo = $I->grabService(StrategyRepository::class);

        $path = '/multi';
        $em->createQueryBuilder()
            ->delete(Strategy::class, 's')
            ->where('s.path = :path')
            ->setParameter('path', $path)
            ->getQuery()
            ->execute();
        $em->clear();

        $s1 = new Strategy();
        $s1->setPath($path);
        $s1->setPriority(5);

        $s2 = new Strategy();
        $s2->setPath($path);
        $s2->setPriority(20);

        $sOther = new Strategy();
        $sOther->setPath('/other');
        $sOther->setPriority(100);

        $em->persist($s1);
        $em->persist($s2);
        $em->persist($sOther);
        $em->flush();
        $em->clear();

        $results = $repo->fetchForPath($path);

        $I->assertCount(2, $results);
        /** @var Strategy $first */
        [$first, $second] = $results;

        $I->assertEquals(20, $first->getPriority());
        $I->assertEquals(5, $second->getPriority());
        foreach ($results as $item) {
            $I->assertEquals($path, $item->getPath());
        }
    }

    public function fetchForPathLoadsConditionsAndAction(FunctionalTester $I): void
    {
        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);
        /** @var StrategyRepository $repo */
        $repo = $I->grabService(StrategyRepository::class);

        $path = '/with-relations';
        $em->createQueryBuilder()
            ->delete(Strategy::class, 's')
            ->where('s.path = :path')
            ->setParameter('path', $path)
            ->getQuery()
            ->execute();
        $em->clear();

        $strategy = new Strategy();
        $strategy->setPath($path);
        $strategy->setPriority(10);

        $cond1 = new Condition();
        $cond1->setHandlerTag('cond_tag_1');
        $cond1->setParameters(['foo' => 'bar']);
        $cond1->setStrategy($strategy);
        $strategy->addCondition($cond1);

        $cond2 = new Condition();
        $cond2->setHandlerTag('cond_tag_2');
        $cond2->setParameters(['baz' => 'qux']);
        $cond2->setStrategy($strategy);
        $strategy->addCondition($cond2);

        $action = new Action();
        $action->setHandlerTag('act_tag');
        $action->setParameters(['url' => 'https://example.com']);
        $action->setStrategy($strategy);
        $strategy->setAction($action);

        $em->persist($strategy);
        $em->persist($cond1);
        $em->persist($cond2);
        $em->persist($action);
        $em->flush();
        $em->clear();

        $results = $repo->fetchForPath($path);

        $I->assertCount(1, $results);

        /** @var Strategy $fetched */
        $fetched = $results[0];
        $I->assertEquals($path, $fetched->getPath());

        $conds = $fetched->getConditions();
        $I->assertCount(2, $conds);
        $tags = array_map(static fn ($c) => $c->getHandlerTag(), $conds->toArray());
        sort($tags);
        $I->assertEquals(['cond_tag_1', 'cond_tag_2'], $tags);

        $fetchedAction = $fetched->getAction();
        $I->assertNotNull($fetchedAction);
        $I->assertEquals('act_tag', $fetchedAction->getHandlerTag());
        $I->assertEquals(['url' => 'https://example.com'], $fetchedAction->getParameters());
    }

    public function fetchForPathMixOfWithAndWithoutRelations(FunctionalTester $I): void
    {
        /** @var EntityManagerInterface $em */
        $em   = $I->grabService(EntityManagerInterface::class);
        /** @var StrategyRepository $repo */
        $repo = $I->grabService(StrategyRepository::class);

        $path = '/mixed';
        $em->createQueryBuilder()
            ->delete(Strategy::class, 's')
            ->where('s.path = :path')
            ->setParameter('path', $path)
            ->getQuery()
            ->execute();
        $em->clear();

        $s1 = new Strategy();
        $s1->setPath($path);
        $s1->setPriority(5);

        $s2 = new Strategy();
        $s2->setPath($path);
        $s2->setPriority(10);

        $cond = new Condition();
        $cond->setHandlerTag('only_cond');
        $cond->setParameters([]);
        $cond->setStrategy($s2);
        $s2->addCondition($cond);

        $act = new Action();
        $act->setHandlerTag('only_act');
        $act->setParameters([]);
        $act->setStrategy($s2);
        $s2->setAction($act);

        $sOther = new Strategy();
        $sOther->setPath('/other');
        $sOther->setPriority(100);

        $em->persist($s1);
        $em->persist($s2);
        $em->persist($cond);
        $em->persist($act);
        $em->persist($sOther);
        $em->flush();
        $em->clear();

        $results = $repo->fetchForPath($path);
        $I->assertCount(2, $results);

        /** @var Strategy[] $results */
        [$first, $second] = $results;
        $I->assertEquals(10, $first->getPriority());
        $I->assertEquals(5, $second->getPriority());

        $I->assertCount(1, $first->getConditions());
        $I->assertNotNull($first->getAction());

        $I->assertTrue($second->getConditions()->isEmpty());
        $I->assertNull($second->getAction());
    }
}
