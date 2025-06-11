<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace bundle\DependencyInjection;

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\DependencyInjection;

use ErdnaxelaWeb\StaticFakeDesignBundle\DependencyInjection\Configuration as StaticFakeDesignConfiguration;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends SiteAccessConfiguration
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ibexa_design_integration');

        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $systemNode = $this->generateScopeBaseNode($rootNode);
        StaticFakeDesignConfiguration::configureRootNode($systemNode);

        return $treeBuilder;
    }
}
