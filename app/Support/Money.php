<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * All money in this system is stored as integer Rupiah to avoid
 * floating-point rounding issues.
 *
 * Usage:
 *   Money::fromUnits(10000)->amount() // 10000
 *   Money::fromAmount(5000)->add(Money::fromAmount(2500))->amount() // 7500
 */
final class Money
{
    private function __construct(private readonly int $amount) {}

    public static function fromAmount(int $amount): self
    {
        return new self($amount);
    }

    /**
     * @param float|string $units e.g. 10000 or 19999.5
     */
    public static function fromUnits(float|string $units): self
    {
        return new self((int) round((float) $units));
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function units(): int
    {
        return $this->amount;
    }

    public function add(Money $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function subtract(Money $other): self
    {
        return new self($this->amount - $other->amount);
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->amount * $factor));
    }

    /**
     * Apply percentage (e.g. 10 for 10%)
     */
    public function percentage(float $percent): self
    {
        if ($percent < 0) {
            throw new InvalidArgumentException('Percentage cannot be negative.');
        }

        return new self((int) round($this->amount * ($percent / 100)));
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount;
    }

    public function greaterThan(Money $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function lessThan(Money $other): bool
    {
        return $this->amount < $other->amount;
    }

    /**
     * Returns plain Indonesian number format.
     * Example: 1000000 => "1.000.000"
     */
    public function formatted(): string
    {
        return number_format($this->amount, 0, ',', '.');
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
