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

use Ibexa\Contracts\Taxonomy\Value\TaxonomyEntry;

class TaxonomyFilterChoice implements FilterChoiceInterface
{
    /**
     * @param array<string, mixed>  $attr
     */
    public function __construct(
        protected TaxonomyEntry $taxonomyEntry,
        protected mixed $value,
        protected int $count,
        protected array $attr = [],
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

    public function getIdentifier(): string
    {
        return $this->taxonomyEntry->getIdentifier();
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getLabel(): string
    {
        return str_replace(
            ['%name%', '%identifier%', '%value%', '%count%'],
            [
                $this->taxonomyEntry->name,
                $this->taxonomyEntry->identifier,
                (string) $this->value,
                (string) $this->count,
            ],
            $this->labelFormat
        );
    }
}
