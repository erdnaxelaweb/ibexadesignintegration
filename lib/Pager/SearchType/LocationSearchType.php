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

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType;

use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;

class LocationSearchType extends ContentSearchType
{
    public function initializeQuery(): void
    {
        $this->query = new LocationQuery();
    }
}
