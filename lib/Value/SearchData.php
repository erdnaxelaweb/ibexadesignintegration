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

class SearchData
{
    /**
     * @param array<string, mixed>       $filters
     */
    public function __construct(
        public array $filters = [],
        public ?string $sort = null,
    ) {
    }

    /**
     * @param array<string, mixed> $request
     */
    public static function createFromRequest(array $request): SearchData
    {
        return new SearchData($request['filters'] ?? [], $request['sort'] ?? null);
    }
}
