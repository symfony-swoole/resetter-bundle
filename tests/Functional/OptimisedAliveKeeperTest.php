<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional;

use Exception;
use RedisCluster;
use ReflectionClass;
use SwooleBundle\ResetterBundle\DBAL\Connection\OptimizedDBALAliveKeeper;
use SwooleBundle\ResetterBundle\Redis\Cluster\Connection\OptimizedRedisClusterAliveKeeper;
use SwooleBundle\ResetterBundle\Tests\Functional\app\OptimisedAliveKeeperTest\ConnectionMock;
use SwooleBundle\ResetterBundle\Tests\Functional\app\OptimisedAliveKeeperTest\RedisClusterSpy;

final class OptimisedAliveKeeperTest extends TestCase
{
    /**
     * @throws Exception
     * @throws Exception
     */
    protected function setUp(): void
    {
        self::bootTestKernel();
    }

    public function testPingIntervalInjectionFromConfiguration(): void
    {
        $doctrineHandlerSvcId = sprintf('%s_%s', OptimizedDBALAliveKeeper::class, 'default');
        /** @var OptimizedDBALAliveKeeper $handler */
        $handler = self::getContainer()->get($doctrineHandlerSvcId);
        $refl = new ReflectionClass(OptimizedDBALAliveKeeper::class);
        $intervalParam = $refl->getProperty('pingIntervalInSeconds');

        self::assertSame(10, $intervalParam->getValue($handler));

        $redisHandlerSvcId = sprintf('%s_%s', OptimizedRedisClusterAliveKeeper::class, 'default');
        /** @var OptimizedRedisClusterAliveKeeper $handler */
        $handler = self::getContainer()->get($redisHandlerSvcId);
        $refl2 = new ReflectionClass(OptimizedRedisClusterAliveKeeper::class);
        $intervalParam2 = $refl2->getProperty('pingIntervalInSeconds');

        self::assertSame(10, $intervalParam2->getValue($handler));
    }

    public function testThatOnlyFirstPingWillBeMadeIn10SecondsOnRequestStart(): void
    {
        $client = self::createClient();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var ConnectionMock $connection */
        $connection = $em->getConnection();
        /** @var RedisClusterSpy $redisCluster */
        $redisCluster = self::getContainer()->get(RedisCluster::class);

        self::assertFalse($connection->isConnected());
        self::assertSame(1, $redisCluster->getConstructorCalls());
        $connection->getNativeConnection(); // simulates real connection usage, calls connect() internally
        $client->request('GET', '/dummy'); // this action does nothing with the database
        self::assertTrue($connection->isConnected());
        self::assertSame(1, $connection->getQueriesCount());
        self::assertSame(1, $redisCluster->getConstructorCalls());
        self::assertSame(1, $redisCluster->getPingCount());

        $client->request('GET', '/dummy'); // this action does nothing with the database
        self::assertSame(1, $connection->getQueriesCount());
        self::assertSame(1, $redisCluster->getConstructorCalls());
        self::assertSame(1, $redisCluster->getPingCount());
    }

    protected static function getTestCase(): string
    {
        return 'OptimisedAliveKeeperTest';
    }
}
