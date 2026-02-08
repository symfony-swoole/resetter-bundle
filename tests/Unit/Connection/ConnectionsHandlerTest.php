<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\Connection;

use PHPUnit\Framework\TestCase;
use SwooleBundle\ResetterBundle\Connection\ConnectionsHandler;
use SwooleBundle\ResetterBundle\Connection\PlatformAliveKeeper;

final class ConnectionsHandlerTest extends TestCase
{
    public function testKeepAliveAllConnections(): void
    {
        $keeper1 = $this->createMock(PlatformAliveKeeper::class);
        $keeper1->expects($this->once())->method('keepAlive');
        $keeper2 = $this->createMock(PlatformAliveKeeper::class);
        $keeper2->expects($this->once())->method('keepAlive');

        $handler = new ConnectionsHandler([$keeper1, $keeper2]);
        $handler->initialize();
    }
}
