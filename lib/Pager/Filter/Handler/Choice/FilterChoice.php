<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\Choice;

class FilterChoice implements FilterChoiceInterface
{
    /**
     * @param array<string, mixed>  $attr
     */
    public function __construct(
        protected string $name,
        protected mixed $value,
        protected int $count,
        protected array $attr = [],
        protected string $labelFormat = '%name% (%count%)'
    ) {
    }

    public function getLabel(): string
    {
        return str_replace(
            ['%name%', '%value%', '%count%'],
            [$this->name, (string) $this->value, (string) $this->count],
            $this->labelFormat
        );
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttr(): array
    {
        return $this->attr;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
