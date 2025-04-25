<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle;

use ErdnaxelaWeb\IbexaDesignIntegrationBundle\DependencyInjection\Compiler\BlockAttributeValueTransformerPass;
use ErdnaxelaWeb\IbexaDesignIntegrationBundle\DependencyInjection\Compiler\FieldValueTransformerPass;
use ErdnaxelaWeb\IbexaDesignIntegrationBundle\DependencyInjection\Compiler\SearchFieldTypesMapPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IbexaDesignIntegrationBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new FieldValueTransformerPass());
        $container->addCompilerPass(new BlockAttributeValueTransformerPass());
        $container->addCompilerPass(new SearchFieldTypesMapPass());
    }
}
