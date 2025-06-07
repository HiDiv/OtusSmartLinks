<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Action;
use App\Entity\Condition;
use App\Entity\Strategy;
use App\Repository\StrategyRepository;
use App\Tests\Support\FunctionalTester;
use Codeception\Attribute\DataProvider;
use Codeception\Example;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class PipelineCest
{
    public function getNonexistentPathReturns404(FunctionalTester $I): void
    {
        $I->amOnPage('/this-path-does-not-exist');

        $I->seeResponseCodeIs(404);
        $I->seeResponseContains('Resource not found');
    }

    public function postAnyPathReturns405(FunctionalTester $I): void
    {
        $I->sendPOST('/some-path', ['foo' => 'bar']);

        $I->seeResponseCodeIs(405);
        $I->seeHttpHeader('Allow', 'GET');
        $I->seeResponseContains('Method Not Allowed. Only GET is supported.');
    }

    #[DataProvider('testSmartRedirectProviderThreeIntervals')]
    public function testSmartRedirectThreeIntervals(FunctionalTester $I, Example $example): void
    {
        $smartUrl = '/invitation_to_the_exhibition';
        $beginDate = $example['beginDate'];
        $endDate = $example['endDate'];
        $redirects = [
            'redirectUrlBefore' => 'http://www.test.org/the_exhibition_will_start_soon/',
            'redirectUrlNow' => 'http://www.test.org/there_is_an_exhibition_going_on_now/',
            'redirectUrlAfter' => 'http://www.test.org/an_exhibition_took_place_recently/',
        ];
        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);

        $strategyBefore = new Strategy();
        $strategyBefore->setPath($smartUrl);
        $strategyBefore->setPriority(30);

        $strategyBeforeCond = new Condition();
        $strategyBeforeCond->setHandlerTag('before_timestamp');
        $strategyBeforeCond->setParameters(['timestamp' => $beginDate]);
        $strategyBeforeCond->setStrategy($strategyBefore);
        $strategyBefore->addCondition($strategyBeforeCond);

        $strategyBeforeAction = new Action();
        $strategyBeforeAction->setHandlerTag('redirect');
        $strategyBeforeAction->setParameters(['url' => $redirects['redirectUrlBefore']]);
        $strategyBeforeAction->setStrategy($strategyBefore);
        $strategyBefore->setAction($strategyBeforeAction);

        $em->persist($strategyBefore);
        $em->persist($strategyBeforeCond);
        $em->persist($strategyBeforeAction);
        $em->flush();

        $strategyNow = new Strategy();
        $strategyNow->setPath($smartUrl);
        $strategyNow->setPriority(20);

        $strategyNowCond1 = new Condition();
        $strategyNowCond1->setHandlerTag('after_or_equal_timestamp');
        $strategyNowCond1->setParameters(['timestamp' => $beginDate]);
        $strategyNowCond1->setStrategy($strategyNow);
        $strategyNow->addCondition($strategyNowCond1);

        $strategyNowCond2 = new Condition();
        $strategyNowCond2->setHandlerTag('before_timestamp');
        $strategyNowCond2->setParameters(['timestamp' => $endDate]);
        $strategyNowCond2->setStrategy($strategyNow);
        $strategyNow->addCondition($strategyNowCond2);

        $strategyNowAction = new Action();
        $strategyNowAction->setHandlerTag('redirect');
        $strategyNowAction->setParameters(['url' => $redirects['redirectUrlNow']]);
        $strategyNowAction->setStrategy($strategyNow);
        $strategyNow->setAction($strategyNowAction);

        $em->persist($strategyNow);
        $em->persist($strategyNowCond1);
        $em->persist($strategyNowCond2);
        $em->persist($strategyNowAction);
        $em->flush();

        $strategyAfter = new Strategy();
        $strategyAfter->setPath($smartUrl);
        $strategyAfter->setPriority(10);

        $strategyAfterCond = new Condition();
        $strategyAfterCond->setHandlerTag('after_or_equal_timestamp');
        $strategyAfterCond->setParameters(['timestamp' => $endDate]);
        $strategyAfterCond->setStrategy($strategyAfter);
        $strategyAfter->addCondition($strategyAfterCond);

        $strategyAfterAction = new Action();
        $strategyAfterAction->setHandlerTag('redirect');
        $strategyAfterAction->setParameters(['url' => $redirects['redirectUrlAfter']]);
        $strategyAfterAction->setStrategy($strategyAfter);
        $strategyAfter->setAction($strategyAfterAction);

        $em->persist($strategyAfter);
        $em->persist($strategyAfterCond);
        $em->persist($strategyAfterAction);
        $em->flush();

        $em->clear();

        $I->stopFollowingRedirects();
        $I->sendGET($smartUrl);

        $I->seeResponseCodeIs(302);
        $I->seeHttpHeader('Location', $redirects[$example['redirectUrl']]);
    }

    #[DataProvider('testSmartRedirectProviderTwoIntervals')]
    public function testSmartRedirectTwoIntervalsWithDefaultCondition(FunctionalTester $I, Example $example): void
    {
        $smartUrl = '/limited_promotion';
        $beforeDate = $example['beforeDate'];

        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);

        $strategyWorks = new Strategy();
        $strategyWorks->setPath($smartUrl);
        $strategyWorks->setPriority(20);

        $strategyWorksCond = new Condition();
        $strategyWorksCond->setHandlerTag('before_timestamp');
        $strategyWorksCond->setParameters(['timestamp' => $beforeDate]);
        $strategyWorksCond->setStrategy($strategyWorks);
        $strategyWorks->addCondition($strategyWorksCond);

        $strategyWorksAction = new Action();
        $strategyWorksAction->setHandlerTag('redirect');
        $strategyWorksAction->setParameters(['url' => 'http://www.test.org/information_about_the_promotion/']);
        $strategyWorksAction->setStrategy($strategyWorks);
        $strategyWorks->setAction($strategyWorksAction);

        $em->persist($strategyWorks);
        $em->persist($strategyWorksCond);
        $em->persist($strategyWorksAction);
        $em->flush();

        $strategyOver = new Strategy();
        $strategyOver->setPath($smartUrl);
        $strategyOver->setPriority(10);

        $strategyOverAction = new Action();
        $strategyOverAction->setHandlerTag('gone');
        $strategyOverAction->setStrategy($strategyOver);
        $strategyOver->setAction($strategyOverAction);

        $em->persist($strategyOver);
        $em->persist($strategyOverAction);
        $em->flush();

        $em->clear();

        $I->stopFollowingRedirects();
        $I->sendGET($smartUrl);

        $I->seeResponseCodeIs($example['code']);
    }

    protected function testSmartRedirectProviderThreeIntervals(): array
    {
        $now = new DateTimeImmutable('now');
        $before1 = $now->modify('-1 week')->format('c');
        $before2 = $now->modify('-2 week')->format('c');
        $after1 = $now->modify('+1 week')->format('c');
        $after2 = $now->modify('+2 week')->format('c');

        return [
            'The event will start soon' => [
                'beginDate' => $after1,
                'endDate' => $after2,
                'redirectUrl' => 'redirectUrlBefore',
            ],
            'The event is currently ongoing' => [
                'beginDate' => $before1,
                'endDate' => $after1,
                'redirectUrl' => 'redirectUrlNow',
            ],
            'The event has already taken place' => [
                'beginDate' => $before2,
                'endDate' => $before1,
                'redirectUrl' => 'redirectUrlAfter',
            ],
        ];
    }

    protected function testSmartRedirectProviderTwoIntervals(): array
    {
        $now = new DateTimeImmutable('now');
        $before = $now->modify('-1 week')->format('c');
        $after = $now->modify('+1 week')->format('c');

        return [
            'The promotion is still going on' => ['beforeDate' => $after, 'code' => 302],
            'The promotion has already ended' => ['beforeDate' => $before, 'code' => 410],
        ];
    }
}
