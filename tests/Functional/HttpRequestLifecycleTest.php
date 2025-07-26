<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Functional;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RedisCluster;
use ReflectionClass;
use SwooleBundle\ResetterBundle\ORM\ResettableEntityManager;
use SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\ConnectionMock;
use SwooleBundle\ResetterBundle\Tests\Functional\app\HttpRequestLifecycleTest\EntityManagerChecker;
use Symfony\Component\HttpKernel\Kernel;

final class HttpRequestLifecycleTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        self::deleteTmpDir();
    }

    public function testDoNotPingConnectionsOnRequestStartIfConnectionIsNotOpen(): void
    {
        $this->setUpInternal();
        $client = self::createClient();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.default_entity_manager');
        $connection = $em->getConnection();
        $redisCluster = self::getContainer()->get(RedisCluster::class);
        $redisCluster->setIsProxyInitialized(false);

        self::assertFalse($connection->isConnected());
        self::assertFalse($redisCluster->wasConstructorCalled());
        $client->request('GET', '/dummy'); // this action does nothing with the database
        self::assertFalse($connection->isConnected());
        // redis cluster does not provide conenction instance without creating the connection
        self::assertFalse($redisCluster->wasConstructorCalled());
    }

    public function testPingConnectionsOnRequestStart(): void
    {
        $this->setUpInternal('configs/config-conn-mock.yaml');
        $client = self::createClient([], 'configs/config-conn-mock.yaml');

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.default_entity_manager');
        /** @var ConnectionMock $connection */
        $connection = $em->getConnection();
        /** @var EntityManagerInterface $emExcluded */
        $emExcluded = self::getContainer()->get('doctrine.orm.excluded_entity_manager');
        /** @var ConnectionMock $connectionExcluded */
        $connectionExcluded = $emExcluded->getConnection();
        $redisCluster = self::getContainer()->get(RedisCluster::class);
        $redisClusterExcluded = self::getContainer()->get(RedisCluster::class . '2');

        self::assertFalse($connection->isConnected());
        self::assertFalse($connectionExcluded->isConnected());
        self::assertFalse($redisCluster->wasConstructorCalled());
        self::assertFalse($redisClusterExcluded->wasConstructorCalled());
        $connection->getNativeConnection(); // simulates real connection usage, calls connect() internally
        $connectionExcluded->getNativeConnection(); // simulates real connection usage, calls connect() internally
        $client->request('GET', '/dummy'); // this action does nothing with the database
        self::assertTrue($connection->isConnected());
        self::assertSame('SELECT 1', $connection->getQuery());
        self::assertNull($connectionExcluded->getQuery());
        self::assertTrue($connectionExcluded->isConnected());
        self::assertTrue($redisCluster->wasConstructorCalled());
        self::assertSame(
            $redisCluster->getConstructorParametersFirst(),
            $redisCluster->getConstructorParametersSecond()
        );
        self::assertFalse($redisClusterExcluded->wasConstructorCalled());
    }

    public function testCheckIfConnectionsHaveActiveTransactionsOnRequestStart(): void
    {
        $this->setUpInternal('configs/config-trans-check.yaml');
        $client = self::createClient([], 'configs/config-trans-check.yaml');

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.default_entity_manager');
        $connection = $em->getConnection();
        /** @var EntityManagerInterface $emExcluded */
        $emExcluded = self::getContainer()->get('doctrine.orm.excluded_entity_manager');
        $connectionExcluded = $emExcluded->getConnection();

        self::assertFalse($connection->isConnected());
        self::assertFalse($connectionExcluded->isConnected());
        $connection->getNativeConnection(); // simulates real connection usage, calls connect() internally
        $connection->beginTransaction();
        $connectionExcluded->getNativeConnection(); // simulates real connection usage, calls connect() internally
        $connectionExcluded->beginTransaction();
        self::assertTrue($connection->isTransactionActive());
        self::assertTrue($connectionExcluded->isTransactionActive());
        $client->request('GET', '/dummy'); // this action does nothing with the database
        self::assertTrue($connection->isConnected());
        self::assertFalse($connection->isTransactionActive());
        self::assertTrue($connectionExcluded->isConnected());
        self::assertTrue($connectionExcluded->isTransactionActive());
    }

    /**
     * @throws Exception
     */
    public function testEmWillBeResetWithServicesResetter(): void
    {
        $this->setUpInternal();
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.default_entity_manager');
        self::assertInstanceOf(ResettableEntityManager::class, $em);

        $client = self::createClient();
        $checker = $client->getContainer()->get(EntityManagerChecker::class . '.default');
        $client->disableReboot();
        $client->request('GET', '/');

        self::assertSame(1, $checker->getNumberOfChecks());
        self::assertTrue($checker->wasEmptyOnLastCheck());

        $client->request('GET', '/');

        self::assertSame(2, $checker->getNumberOfChecks());
        self::assertTrue($checker->wasEmptyOnLastCheck());
    }

    /**
     * @throws Exception
     */
    public function testEmWillBeResetOnErrorWithServicesResetter(): void
    {
        $this->setUpInternal();
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.default_entity_manager');
        self::assertInstanceOf(ResettableEntityManager::class, $em);
        $refl = new ReflectionClass(ResettableEntityManager::class);
        $wrappedProperty = $refl->getProperty('wrapped');
        $wrappedProperty->setAccessible(true);
        $wrapped = $wrappedProperty->getValue($em);

        $client = self::createClient();
        /** @var EntityManagerChecker $checker */
        $checker = $client->getContainer()->get(EntityManagerChecker::class . '.default');
        $client->disableReboot();
        $client->request('GET', '/persist-error');

        self::assertSame(1, $checker->getNumberOfChecks());
        self::assertTrue($checker->wasEmptyOnLastCheck());

        $client->request('GET', '/persist-error');

        self::assertSame(2, $checker->getNumberOfChecks());
        self::assertTrue($checker->wasEmptyOnLastCheck());

        $client->request('GET', '/persist-error');

        self::assertSame(3, $checker->getNumberOfChecks());
        self::assertTrue($checker->wasEmptyOnLastCheck());

        /** @var EntityManagerInterface $wrapped2 */
        $wrapped2 = $wrappedProperty->getValue($em);
        self::assertSame($wrapped, $wrapped2);
        self::assertTrue($wrapped2->isOpen());

        $response = $client->request('GET', '/remove-all');

        self::assertSame(4, $checker->getNumberOfChecks());
        self::assertTrue($checker->wasEmptyOnLastCheck());
        self::assertSame(0, $response->count()); // this means that there was an empty response
    }

    /**
     * @throws Exception
     */
    public function testExcludedEmWillBeResetOnErrorWithServicesResetterButRepositoryWontBeResetted(): void
    {
        if (version_compare(Kernel::VERSION, '6.2.0') >= 0) {
            $this->markTestSkipped('This test is not needed with Symfony 6.2');

            return;
        }

        $this->setUpInternal();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.excluded_entity_manager');
        self::assertNotInstanceOf(ResettableEntityManager::class, $em);

        $client = self::createClient();
        $checker = $client->getContainer()->get(EntityManagerChecker::class . '.excluded');
        $client->disableReboot();
        $client->request('GET', '/persist-error-excluded');

        self::assertSame(1, $checker->getNumberOfChecks());
        self::assertTrue($checker->wasEmptyOnLastCheck());

        $client->request('GET', '/persist-error-excluded');

        self::assertSame(2, $checker->getNumberOfChecks());
        self::assertTrue($checker->wasEmptyOnLastCheck());

        $response = $client->request('GET', '/remove-all-excluded');

        self::assertTrue($checker->wasEmptyOnLastCheck());
        self::assertNotSame(0, $response->count());
        self::assertStringContainsString(
            'Detached entity SwooleBundle\\ResetterBundle\\Tests\\Functional\\app\\HttpRequestLifecycleTest\\ExcludedEntity\\ExcludedTestEntity2', // phpcs:ignore
            $response->html()
        );
    }

    /**
     * @throws Exception
     */
    public function testExcludedEmWontBeWrappedAndWillBeResetWithDefaultDoctrineServicesResetter(): void
    {
        $this->setUpInternal();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.excluded_entity_manager');
        self::assertInstanceOf(EntityManager::class, $em);

        $client = self::createClient();
        $checker = $client->getContainer()->get(EntityManagerChecker::class . '.excluded');
        $client->disableReboot();
        $client->request('GET', '/persist-excluded');

        self::assertSame(1, $checker->getNumberOfChecks());
        self::assertTrue($checker->wasEmptyOnLastCheck());

        $client->request('GET', '/persist-excluded');

        self::assertSame(2, $checker->getNumberOfChecks());
        self::assertTrue($checker->wasEmptyOnLastCheck());
    }

    protected static function getTestCase(): string
    {
        return 'HttpRequestLifecycleTest';
    }

    private function setUpInternal(string $rootConfig = 'configs/config.yaml'): void
    {
        self::bootTestKernel($rootConfig);
        self::runCommand('cache:clear --no-warmup');
        self::runCommand('cache:warmup');
        self::runCommand('doctrine:database:drop --force --connection default');
        self::runCommand('doctrine:schema:create --em default');
        self::runCommand('doctrine:database:drop --force --connection excluded');
        self::runCommand('doctrine:schema:create --em excluded');
    }
}
