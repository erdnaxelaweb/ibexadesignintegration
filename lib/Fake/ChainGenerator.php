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
