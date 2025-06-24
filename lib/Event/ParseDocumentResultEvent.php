<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Event;

use ErdnaxelaWeb\StaticFakeDesign\Value\Document;

class ParseDocumentResultEvent
{
    public const ON_PARSE = "erdnaxelaweb.ibexa_design_integration.document_search.parse_result";
    public function __construct(
        public object $source,
        public Document $document,
    ) {
    }
}
