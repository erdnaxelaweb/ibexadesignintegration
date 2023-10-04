<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaHtmlIntegrationBundle/blob/master/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

class GenericFieldValueTransformer implements FieldValueTransformerInterface
{
    public function transformFieldValue(
        Content $content,
        string $fieldIdentifier,
        FieldDefinition $fieldDefinition
    ) {
        return (string) $content->getFieldValue($fieldIdentifier);
    }
}
