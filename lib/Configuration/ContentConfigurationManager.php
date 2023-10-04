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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Configuration;

use ErdnaxelaWeb\StaticFakeDesign\Configuration\ContentConfigurationManager as BaseContentConfigurationManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentConfigurationManager extends BaseContentConfigurationManager
{
    protected function configureFieldOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureFieldOptions($optionsResolver);

        $optionsResolver->define('name')
            ->required()
            ->allowedTypes('string', 'array');

        $optionsResolver->define('description')
            ->default('')
            ->allowedTypes('string', 'array');

        $optionsResolver->define('searchable')
            ->default(false)
            ->allowedTypes('bool');

        $optionsResolver->define('infoCollector')
            ->default(false)
            ->allowedTypes('bool');

        $optionsResolver->define('translatable')
            ->default(true)
            ->allowedTypes('bool');

        $optionsResolver->define('category')
            ->default('content')
            ->allowedTypes('string');
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->define('name')
            ->required()
            ->allowedTypes('string', 'array');

        $optionsResolver->define('description')
            ->default('')
            ->allowedTypes('string', 'array');

        $optionsResolver->define('nameSchema')
            ->default('')
            ->allowedTypes('string');

        $optionsResolver->define('urlAliasSchema')
            ->default('')
            ->allowedTypes('string');

        $optionsResolver->define('container')
            ->default(false)
            ->allowedTypes('bool');

        $optionsResolver->define('defaultAlwaysAvailable')
            ->default(false)
            ->allowedTypes('bool');

        $optionsResolver->define('defaultSortField')
            ->default('published')
            ->allowedTypes('string')
            ->allowedValues(
                'path',
                'published',
                'modified',
                'section',
                'depth',
                'class_identifier',
                'class_name',
                'priority',
                'name'
            );

        $optionsResolver->define('defaultSortOrder')
            ->default('desc')
            ->allowedTypes('string')
            ->allowedValues('desc', 'asc');
    }
}
