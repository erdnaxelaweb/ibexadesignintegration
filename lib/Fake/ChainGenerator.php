<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Fake;

use ErdnaxelaWeb\StaticFakeDesign\Fake\ChainGenerator as BaseGenerator;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Contracts\Service\Attribute\Required;

class ChainGenerator extends BaseGenerator
{
    protected ConfigResolverInterface $configResolver;

    #[Required]
    public function setConfigResolver(ConfigResolverInterface $configResolver): void
    {
        $this->configResolver = $configResolver;
    }

    public function isFakeGenerationEnabled(): bool
    {
        return $this->configResolver->getParameter('enable_fake_generation', 'ibexa_design_integration');
    }
}
