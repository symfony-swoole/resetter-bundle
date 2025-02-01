<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\DependencyInjection;

interface Parameters
{
    public const EXCLUDED_FROM_PROCESSING_ENTITY_MANAGERS =
        'swoole_bundle_resetter.excluded_from_processing.entity_managers';

    public const EXCLUDED_FROM_PROCESSING_DBAL_CONNECTIONS =
        'swoole_bundle_resetter.excluded_from_processing.connections.dbal';

    public const EXCLUDED_FROM_PROCESSING_REDIS_CLUSTER_CONNECTIONS =
        'swoole_bundle_resetter.excluded_from_processing.connections.redis_cluster';

    public const PING_INTERVAL = 'swoole_bundle_resetter.ping_interval';

    public const CHECK_ACTIVE_TRANSACTIONS = 'swoole_bundle_resetter.check_active_transactions';
}
