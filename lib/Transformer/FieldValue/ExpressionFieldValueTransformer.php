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
use ErdnaxelaWeb\StaticFakeDesign\Expression\ExpressionResolver;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

class ExpressionFieldValueTransformer extends AbstractFieldValueTransformer
{
    public function __construct(
        protected ExpressionResolver $expressionResolver,
    ) {
    }

    public function support(?string $ibexaFieldTypeIdentifier): bool
    {
        return $ibexaFieldTypeIdentifier === null;
    }


    protected function transformFieldValue(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        ?FieldDefinition       $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ) {
        $expression = $contentFieldDefinition->getOption('expression');
        return ($this->expressionResolver)(
            [
                'content' => $content,
            ],
            $expression
        );
    }
}
