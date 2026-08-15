<?php

namespace App\Playground;

class RentalHistoryRecord
{
    public function __construct(
        public string $equipmentName,
        public string $staffName,
        public \DateTimeImmutable $checkedOutAt,
        private string $internalNote = 'not persisted', // deliberately excluded from serialization
    ) {}

    /**
     * Only these properties are serialized; $internalNote is intentionally
     * omitted so it never round-trips through serialize().
     *
     * @return string[]
     */
    public function __sleep(): array
    {
        return ['equipmentName', 'staffName', 'checkedOutAt'];
    }

    /**
     * Re-derive the excluded property on wakeup instead of persisting it.
     */
    public function __wakeup(): void
    {
        $this->internalNote = 'restored';
    }

    public function internalNote(): string
    {
        return $this->internalNote;
    }
}
