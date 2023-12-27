<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

class SearchData
{
    public function __construct(
        public array $filters = [],
        public ?string $sort = null,
    ) {
    }

    public static function createFromRequest(array $request): SearchData
    {
        return new SearchData($request['filters'] ?? [], $request['sort'] ?? null);
    }
}
