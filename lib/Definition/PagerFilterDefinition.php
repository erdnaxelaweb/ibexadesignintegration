<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Definition;

use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionOptions;
use ErdnaxelaWeb\StaticFakeDesign\Definition\PagerFilterDefinition as NativePagerFilterDefinition;

class PagerFilterDefinition extends NativePagerFilterDefinition
{
    /**
     * @param array<string, PagerFilterDefinition>                                                       $nested
     */
    public function __construct(
        string $identifier,
        string $type,
        DefinitionOptions $options,
        protected readonly string $criterionType,
        protected readonly array $nested,
    ) {
        parent::__construct($identifier, $type, $options);
    }

    public function getCriterionType(): string
    {
        return $this->criterionType;
    }

    /**
     * @return array<string, PagerFilterDefinition>
     */
    public function getNestedFilters(): array
    {
        return $this->nested;
    }
}
