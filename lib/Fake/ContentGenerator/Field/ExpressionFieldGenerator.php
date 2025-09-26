<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Fake\ContentGenerator\Field;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpressionFieldGenerator extends \ErdnaxelaWeb\StaticFakeDesign\Fake\ContentGenerator\Field\ExpressionFieldGenerator
{
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->define('cacheTagsExpression')->default(null)->allowedTypes('string', 'string[]', 'null');
    }
}
