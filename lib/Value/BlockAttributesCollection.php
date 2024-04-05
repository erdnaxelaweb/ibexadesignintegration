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

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttributeValueTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Value\BlockAttributesCollection as BaseBlockAttributesCollection;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;

class BlockAttributesCollection extends BaseBlockAttributesCollection
{
    protected array $initState = [];

    public function __construct(
        protected BlockValue                     $blockValue,
        protected BlockDefinition                $blockDefinition,
        protected array                          $blockAttributesConfiguration,
        protected BlockAttributeValueTransformer $blockAttributeTransformer
    ) {
        parent::__construct();

        foreach ($blockAttributesConfiguration as $attributeIdentifier => $attributeConfiguration) {
            $this->initState[$attributeIdentifier] = false;
            $this->set($attributeIdentifier, null);
        }
    }

    public function get(string|int $key)
    {
        if (isset($this->initState[$key]) && $this->initState[$key] === false) {
            $this->initState[$key] = true;
            $this->set(
                $key,
                $this->blockAttributeTransformer->transform(
                    $this->blockValue,
                    $this->blockDefinition,
                    $key,
                    $this->blockAttributesConfiguration[$key]
                )
            );
        }
        return parent::get($key);
    }
}
