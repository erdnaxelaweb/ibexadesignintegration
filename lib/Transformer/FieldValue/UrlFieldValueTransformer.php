<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Knp\Menu\FactoryInterface;

class UrlFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected FactoryInterface $factory,
    ) {
    }

    public function transformFieldValue(
        AbstractContent $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        array           $fieldConfiguration
    ) {
        /** @var \Ibexa\Core\FieldType\Url\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        $name = $fieldValue->text ?? 'link';
        $options = [
            'uri' => $fieldValue->link,
            'linkAttributes' => [],
        ];

        return $this->factory->createItem($name, $options);
    }
}
