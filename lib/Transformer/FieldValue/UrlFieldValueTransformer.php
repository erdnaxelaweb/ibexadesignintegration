<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class UrlFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected FactoryInterface $factory,
    ) {
    }

    public function transformFieldValue(
        AbstractContent $content,
        string $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): ItemInterface {
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
