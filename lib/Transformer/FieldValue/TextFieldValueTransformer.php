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

class TextFieldValueTransformer
{
    public function transformFieldValue(
        Content         $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition
    ) {
        /** @var \Ibexa\Core\FieldType\TextBlock\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        return sprintf('<p>%s</p>', nl2br($fieldValue->text));
    }
}
