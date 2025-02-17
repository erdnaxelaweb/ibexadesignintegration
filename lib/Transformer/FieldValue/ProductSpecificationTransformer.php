<?php

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\ProductCatalog\Local\LocalProductServiceInterface;

class ProductSpecificationTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected LocalProductServiceInterface $productService
    ) {
    }

    public function transformFieldValue(
        AbstractContent $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        array           $fieldConfiguration
    ) {
        return $this->productService->getProductFromContent($content);
    }
}
