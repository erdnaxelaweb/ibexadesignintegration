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

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

class GenericFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected string $propertyName = "value"
    ) {
    }

    public function transformFieldValue(
        Content $content,
        string $fieldIdentifier,
        FieldDefinition $fieldDefinition
    ) {
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        return $fieldValue->{$this->propertyName};
    }
}
