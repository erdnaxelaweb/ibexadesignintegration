<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use ErdnaxelaWeb\StaticFakeDesign\Value\Block as BaseBlock;
use ErdnaxelaWeb\StaticFakeDesign\Value\BlockAttributesCollection;

class Block extends BaseBlock
{
    public function __construct(
        int                       $id,
        string                    $name,
        string                    $type,
        string                    $view,
        BlockAttributesCollection $attributes,
        public readonly int $contentId,
        public readonly int $locationId,
        public readonly int $versionNo,
        public readonly string $languageCode,
    ) {
        parent::__construct($id, $name, $type, $view, $attributes);
    }
}
