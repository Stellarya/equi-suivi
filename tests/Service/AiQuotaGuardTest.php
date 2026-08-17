<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\AiUsage;
use App\Exception\QuotaExceededException;
use App\Repository\AiUsageRepository;
use App\Service\AiQuotaGuard;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class AiQuotaGuardTest extends TestCase
{
    public function testCallIsAllowedBelowTheDailyLimit(): void
    {
        $usage = new AiUsage();
        $usage->setCallCount(3);

        $this->guard($usage, limit: 50)->assertCanCall();

        $this->expectNotToPerformAssertions();
    }

    public function testCallIsRefusedOnceTheLimitIsReached(): void
    {
        $usage = new AiUsage();
        $usage->setCallCount(50);

        $this->expectException(QuotaExceededException::class);

        $this->guard($usage, limit: 50)->assertCanCall();
    }

    public function testRecordCallIncrementsTodayCounter(): void
    {
        $usage = new AiUsage();
        $usage->setCallCount(7);

        $this->guard($usage, limit: 50)->recordCall();

        self::assertSame(8, $usage->getCallCount());
    }

    private function guard(AiUsage $usage, int $limit): AiQuotaGuard
    {
        $repository = $this->createMock(AiUsageRepository::class);
        $repository->method('findOneBy')->willReturn($usage);

        return new AiQuotaGuard($repository, $this->createMock(EntityManagerInterface::class), $limit);
    }
}