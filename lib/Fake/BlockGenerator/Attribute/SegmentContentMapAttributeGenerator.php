<?php

/*
 * ibexadesignbundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Fake\BlockGenerator\Attribute;

use ErdnaxelaWeb\StaticFakeDesign\Fake\BlockGenerator\Attribute\AbstractAttributeGenerator;
use ErdnaxelaWeb\StaticFakeDesign\Fake\Generator\ContentGenerator;
use ErdnaxelaWeb\StaticFakeDesign\Value\Content;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SegmentContentMapAttributeGenerator extends AbstractAttributeGenerator
{
    public function __construct(
        protected ContentGenerator $contentGenerator
    ) {
    }

    /**
     * @param string|string[] $type
     */
    public function __invoke(array|string $type, int $max = 1): ?Content
    {
        return ($this->contentGenerator)($type);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->define('type')
                        ->required()
                        ->allowedTypes('string', 'string[]');
    }
}
