<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Definition\Transformer;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Sort\ChainSortHandler;
use ErdnaxelaWeb\StaticFakeDesign\Definition\Transformer\PagerSortDefinitionTransformer as NativePagerSortDefinitionTransformer;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PagerSortDefinitionTransformer extends NativePagerSortDefinitionTransformer
{
    public function __construct(
        protected ChainSortHandler $sortHandler,
    ) {
    }

    public function configureOptions(OptionsResolver $optionsResolver, array $options): void
    {
        parent::configureOptions($optionsResolver, $options);

        $optionsResolver->setAllowedValues('type', $this->sortHandler->getTypes());

        $optionsResolver->setNormalizer(
            'options',
            function (Options $options, $fieldDefinitionOptions) {
                $optionsResolver = new OptionsResolver();
                $this->sortHandler->configureOptions($options['type'], $optionsResolver);
                return $optionsResolver->resolve($fieldDefinitionOptions);
            }
        );
    }
}
