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
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('parameters.yaml');
        $loader->load('services.yaml');
        $loader->load('pager.yaml');
        $loader->load('pager_sorts_handlers.yaml');
        $loader->load('pager_filter_handlers.yaml');
        $loader->load('transformer.yaml');
        $loader->load('showroom.yaml');

        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));
        if (in_array('eZMigrationBundle', $activatedBundles, true)) {
            $loader->load('kaliop_migration_services.yaml');
        }
        if (in_array('IbexaTaxonomyBundle', $activatedBundles, true)) {
            $loader->load('taxonomy.yaml');
        }
        if (in_array('IbexaFormBuilderBundle', $activatedBundles, true)) {
            $loader->load('form_builder.yaml');
        }
        if (in_array('IbexaFieldTypePageBundle', $activatedBundles, true)) {
            $loader->load('page_builder.yaml');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->addTwigConfiguration($container);
        $this->addImageVariationConfig($container);
    }

    protected function getParameter(ContainerBuilder $container, string $name, $default)
    {
        return $container->hasParameter($name) ? $container->getParameter($name) : $default;
    }

    protected function addImageVariationConfig(ContainerBuilder $container): void
    {
        $variationsConfig = [];

        $useRetina = $this->getParameter($container, 'erdnaxelaweb.static_fake_design.image.use_retina', true);
        $breakpoints = $this->getParameter($container, 'erdnaxelaweb.static_fake_design.image.breakpoints', []);
        $variations = $this->getParameter($container, 'erdnaxelaweb.static_fake_design.image.variations', []);
        foreach ($variations as $variationName => $variationSizes) {
            foreach ($variationSizes as $i => $variationSize) {
                [$variationWidth, $variationHeight] = $variationSize;
                $breakpoint = $breakpoints[$i];
                $variationFullName = "{$variationName}_{$breakpoint['suffix']}";
                $variationsConfig[$variationFullName] = $this->getVariationConfig(
                    $variationWidth,
                    $variationHeight
                );

                if ($useRetina) {
                    $variationRetinaFullName = "{$variationFullName}_retina";
                    $variationsConfig[$variationRetinaFullName] = $this->getVariationConfig(
                        $variationWidth * 2,
                        $variationHeight * 2
                    );
                }
            }
        }

        $container->prependExtensionConfig(
            "ibexa",
            [
                'system' => [
                    'fo_group' => [
                        'image_variations' => $variationsConfig,
                    ],
                ],
            ]
        );
    }

    private function getVariationConfig(?float $width, ?float $height): array
    {
        if (! $height && $width) {
            return [
                'reference' => null,
                'filters' => [
                    [
                        'name' => 'geometry/scalewidthdownonly',
                        'params' => [$width],
                    ],
                ],
            ];
        }
        if (! $width && $height) {
            return [
                'reference' => null,
                'filters' => [
                    [
                        'name' => 'geometry/scaleheightdownonly',
                        'params' => [$height],
                    ],
                ],
            ];
        }
        return [
            'reference' => null,
            'filters' => [
                [
                    'name' => 'focusedThumbnail',
                    'params' => [
                        'size' => [$width, $height],
                        'focus' => [0, 0],
                    ],
                ],
            ],
        ];
    }

    private function addTwigConfiguration(ContainerBuilder $container): void
    {
        if (! $container->hasExtension('twig')) {
            return;
        }

        $path = __DIR__ . '/../Resources/views';
        $container->prependExtensionConfig('twig', [
            'paths' => [$path],
        ]);
    }
}
