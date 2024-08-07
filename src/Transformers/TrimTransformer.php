<?php

declare(strict_types=1);

namespace Jwhulette\Pipes\Transformers;

use Jwhulette\Pipes\Contracts\TransformerInterface;
use Jwhulette\Pipes\DataTransferObjects\TrimDto;
use Jwhulette\Pipes\Exceptions\PipesInvalidArgumentException;
use Jwhulette\Pipes\Frame;

final class TrimTransformer implements TransformerInterface
{
    /** @var array<int,TrimDto> */
    protected array $columns;

    protected bool $allColumns = false;

    protected string $type = 'trim';

    protected string $mask = " \t\n\r\0\x0B";

    public function __invoke(Frame $frame): Frame
    {
        // Apply to all columns
        if ($this->allColumns) {
            $frame->data->transform(
                fn ($item) => $this->trimColumnValue(
                    \strval($item),
                    $this->columns[0]->type,
                    $this->columns[0]->mask
                )
            );

            return $frame;
        }

        // Apply to only selected columns
        $frame->data->transform(function ($item, $key) {
            foreach ($this->columns as $dto) {
                if ($dto->column === $key) {
                    return $this->trimColumnValue(\strval($item), $dto->type, $dto->mask);
                }
            }

            return $item;
        });

        return $frame;
    }

    /**
     * Set the columns and transformation.
     *
     * @param string|null $type [Default: trim][Options: trim, ltrim, rtrim]
     * @param string|null $mask [Default: \t\n\r\0\x0B]
     *
     * @see https://www.php.net/manual/en/function.trim.php
     */
    public function transformColumn(string|int $column, ?string $type = null, ?string $mask = null): self
    {
        $columnType = $type ?? $this->type;
        $columnMask = $mask ?? $this->mask;
        $this->columns[] = new TrimDto($column, $columnType, $columnMask);

        return $this;
    }

    public function transformAllColumns(?string $type = null, ?string $mask = null): self
    {
        $columnType = $type ?? $this->type;
        $columnMask = $mask ?? $this->mask;

        $this->columns[] = new TrimDto(null, $columnType, $columnMask);

        $this->allColumns = true;

        return $this;
    }

    /**
     * @throws PipesInvalidArgumentException
     */
    public function trimColumnValue(?string $value, ?string $type, ?string $mask): string
    {
        if (! \is_callable($type)) {
            throw new PipesInvalidArgumentException("Invalid trim type: {$type}.");
        }

        if (\is_null($value)) {
            return '';
        }

        if (\is_null($mask)) {
            $mask = $this->mask;
        }

        $result = \call_user_func($type, $value, $mask);

        return \strval($result);
    }
}
