<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;

class AggregationGroup implements Aggregation
{
    /**
     * @param Aggregation[] $aggregations
     */
    public function __construct(
        public array $aggregations,
    ) {
    }
    public function getName(): string
    {
        return 'AggregationGroup';
    }
}
