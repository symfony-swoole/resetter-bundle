<?php

declare(strict_types=1);

namespace SwooleBundle\ResetterBundle\Tests\Unit\Helper;

use Override;
use Psr\Log\AbstractLogger;
use Stringable;

final class RecordingLogger extends AbstractLogger
{
    /**
     * @var list<array{string, array<array-key, mixed>}>
     */
    private array $records = [];

    /**
     * Untyped, as psr/log 1.x declares it: a narrower signature would not load against it.
     *
     * @param mixed $level
     * @param string|Stringable $message
     * @param array<array-key, mixed> $context
     */
    #[Override]
    public function log($level, $message, array $context = []): void
    {
        $this->records[] = [(string) $message, $context];
    }

    /**
     * @return list<string>
     */
    public function messages(): array
    {
        return array_column($this->records, 0);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function contextOf(int $index): array
    {
        return $this->records[$index][1];
    }
}
