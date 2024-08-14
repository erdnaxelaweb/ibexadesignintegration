<?php

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\Choice;

use Ibexa\Contracts\Taxonomy\Value\TaxonomyEntry;

class TaxonomyFilterChoice implements FilterChoiceInterface
{
    public function __construct(
        protected TaxonomyEntry $taxonomyEntry,
        protected $value,
        protected int    $count,
        protected array  $attr = [],
        protected string $labelFormat = '%name% (%count%)'
    ) {
    }

    public function getTaxonomyEntry(): TaxonomyEntry
    {
        return $this->taxonomyEntry;
    }

    public function getParent(): string
    {
        return $this->taxonomyEntry->getParent()
            ->getName();
    }

    public function getAttr(): array
    {
        return $this->attr;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return str_replace(
            ['%name%', '%identifier%', '%value%', '%count%'],
            [$this->taxonomyEntry->name, $this->taxonomyEntry->identifier, $this->value, $this->count],
            $this->labelFormat
        );
    }
}
