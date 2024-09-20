<?php

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\Choice;

class FilterChoice implements FilterChoiceInterface
{
    public function __construct(
        protected string $name,
        protected $value,
        protected int    $count,
        protected array  $attr = [],
        protected string $labelFormat = '%name% (%count%)'
    ) {
    }

    public function getLabel(): string
    {
        return str_replace(
            ['%name%', '%value%', '%count%'],
            [$this->name, $this->value, $this->count],
            $this->labelFormat
        );
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getAttr(): array
    {
        return $this->attr;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
