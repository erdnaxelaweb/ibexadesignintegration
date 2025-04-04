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

use ErdnaxelaWeb\IbexaDesignIntegration\Trait\LazyCollection;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentFieldsCollection as BaseContentFieldsCollection;

class ContentFieldsCollection extends BaseContentFieldsCollection
{
    use LazyCollection;
}
