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

use ErdnaxelaWeb\StaticFakeDesign\Configuration\ImageConfiguration;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Exception\ParameterNotFoundException;

class SiteaccessAwareImageConfiguration extends ImageConfiguration
{
    public function __construct(
        protected ConfigResolverInterface $configResolver,
        array                             $configuration
    ) {
        parent::__construct($configuration);
    }

    public function getBreakpoints(): array
    {
        $nativeBreakpoints = parent::getBreakpoints();
        try {
            $saBreakpoints = $this->getSaConfig(
                'breakpoints'
            );
        } catch (ParameterNotFoundException $exception) {
            return $nativeBreakpoints;
        }
        return $saBreakpoints;
    }

    public function getVariations(): array
    {
        $nativeVariations = parent::getVariations();
        try {
            $saVariations = $this->getSaConfig(
                'variations'
            );
        } catch (ParameterNotFoundException $exception) {
            return $nativeVariations;
        }
        return array_merge($nativeVariations, $saVariations);
    }

    /**
     * @return mixed[]
     */
    protected function getSaConfig(string $id): array
    {
        $saImageConfig = $this->configResolver->getParameter(
            'image',
            'ibexa_design_integration'
        );
        if (!isset($saImageConfig[$id])) {
            throw new ParameterNotFoundException('image.' . $id, 'ibexa_design_integration');
        }
        return $saImageConfig[$id];
    }
}
