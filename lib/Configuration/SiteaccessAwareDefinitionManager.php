<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Configuration;

use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Definition\Transformer\DefinitionTransformer;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Exception\ParameterNotFoundException;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessServiceInterface;

class SiteaccessAwareDefinitionManager extends DefinitionManager
{
    public function __construct(
        protected ConfigResolverInterface    $configResolver,
        protected SiteAccessServiceInterface $siteaccessService,
        DefinitionTransformer                $definitionTransformer,
        array                                $definitions = []
    ) {
        parent::__construct($definitionTransformer, $definitions);
    }

    protected function getDefinitionsHashesByType(mixed $type): array
    {
        $nativeDefinitions = parent::getDefinitionsHashesByType($type);
        $parameterName = sprintf('%s_definition', $type);
        try {
            $saDefinitions = $this->configResolver->getParameter(
                $parameterName,
                'ibexa_design_integration'
            );
        } catch (ParameterNotFoundException $exception) {
            return $nativeDefinitions;
        }

        return array_merge($nativeDefinitions, $saDefinitions);
    }

    protected function getDefinitionCacheKey(string $type, string $identifier): string
    {
        $scope = $this->siteaccessService->getCurrent()->name;
        return sprintf('%s-%s-%s', $scope, $type, $identifier);
    }
}
