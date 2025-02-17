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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\FieldTypeRichText\RichText\Converter as RichTextConverterInterface;
use Ibexa\Core\Helper\FieldHelper;

class RichtextFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected RichTextConverterInterface $richTextOutputConverter,
        protected FieldHelper $fieldHelper,
    ) {
    }

    public function transformFieldValue(
        AbstractContent $content,
        string $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        array $fieldConfiguration
    ) {
        /** @var \Ibexa\FieldTypeRichText\FieldType\RichText\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);

        if ($this->fieldHelper->isFieldEmpty($content, $fieldIdentifier)) {
            return null;
        }

        return $this->richTextOutputConverter->convert($fieldValue->xml)
            ->saveHTML();
    }
}
