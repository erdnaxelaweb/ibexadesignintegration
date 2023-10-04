<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaHtmlIntegrationBundle/blob/master/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

class SearchData
{
    public function __construct(
        public readonly array $filters,
        public readonly ?string $sort,
    ) {
    }

    public static function createFromRequest(array $request): SearchData
    {
        return new SearchData($request['filters'] ?? [], $request['sort'] ?? null);
    }
}
