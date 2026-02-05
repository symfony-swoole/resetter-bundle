<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\DBAL\Connection\FailoverAware;

use InvalidArgumentException;

final readonly class ConnectionType
{
    public const string WRITER = 'writer';

    public const string READER = 'reader';

    private const array ALLOWED_TYPES = [
        self::WRITER,
        self::READER,
    ];

    private function __construct(
        private string $type,
    ) {}

    public static function create(string $type): self
    {
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Invalid connection type %s.', $type));
        }

        return new self($type);
    }

    public function isWriter(): bool
    {
        return $this->type === self::WRITER;
    }
}
