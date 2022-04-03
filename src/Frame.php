<?php

declare(strict_types=1);

namespace Jwhulette\Pipes;

use Illuminate\Support\Collection;

/**
 * A data frame.
 */
class Frame
{
    protected Collection $header;
    protected Collection $data;
    protected array $attributes = [];
    protected bool $end = false;

    /**
     * @param array $data
     *
     * @return Frame
     */
    public function setData(array $data): Frame
    {
        $this->data = collect($data);

        if (isset($this->header) && $this->header->isNotEmpty()) {
            $this->data = $this->header->combine($this->data);
        }

        return $this;
    }

    public function getData(): Collection
    {
        return $this->data;
    }

    /**
     * @param array $header
     */
    public function setHeader(array $header): void
    {
        $this->header = collect($header);
    }

    public function getHeader(): Collection
    {
        return $this->header;
    }

    /**
     * Set extra attributes to a data frame for use in processing
     * @param array $attribute
     */
    public function setAttribute(array $attribute): void
    {
        $this->attributes[key($attribute)] = $attribute[key($attribute)];
    }

    public function getAllAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key): mixed
    {
        return  $this->attributes[$key];
    }

    /**
     * Set the frame as the last data element.
     */
    public function setEnd(): void
    {
        $this->end = true;
    }

    public function getEnd(): bool
    {
        return $this->end;
    }
}
