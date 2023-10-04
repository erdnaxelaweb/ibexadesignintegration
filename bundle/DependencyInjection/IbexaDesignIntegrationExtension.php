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

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class IbexaDesignIntegrationExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('pager.yaml');
        $loader->load('transformer.yaml');

        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));
        if (in_array('eZMigrationBundle', $activatedBundles, true)) {
            $loader->load('kaliop_migration_services.yaml');
        }
    }


    public function prepend(ContainerBuilder $container): void
    {
        $this->addTwigConfiguration( $container );
        $this->addImageVariationConfig( $container );
    }


    protected function addImageVariationConfig(ContainerBuilder $container): void
    {
        $variationsConfig = [];
        $breakpoints = $container->getParameter('erdnaxelaweb.static_fake_design.image.breakpoints');
        $variations = $container->getParameter('erdnaxelaweb.static_fake_design.image.variations');
        foreach ( $variations as $variationName => $variationSizes )
        {
            foreach ( $variationSizes as $i=>$variationSize )
            {
                $breakpoint = $breakpoints[$i];
                $variationFullName = "{$variationName}_{$breakpoint['suffix']}";
                $variationsConfig[$variationFullName] = [
                    'reference' => null,
                    'filters' => [
                        ['name' => 'focusedThumbnail', 'params'=> [ 'size' => $variationSize, 'focus' => [0,0]]]
                    ]
                ];
            }
        }

        $container->prependExtensionConfig(
            "ibexa",
            [
                'system' => [
                    'site' => [
                        'image_variations' => $variationsConfig
                    ]
                ]
            ]
        );
    }
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     */
    private function addTwigConfiguration( ContainerBuilder $container ): void
    {
        if ( !$container->hasExtension( 'twig' ) )
        {
            return;
        }

        $path = __DIR__ . '/../Resources/views';
        $container->prependExtensionConfig( 'twig', [ 'paths' => [ $path ] ] );
    }

}
