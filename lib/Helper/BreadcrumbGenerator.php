<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Helper;

use ErdnaxelaWeb\StaticFakeDesign\Value\Breadcrumb;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

class BreadcrumbGenerator
{
    public function __construct(
        protected LinkGenerator $linkGenerator,
        protected ConfigResolverInterface $configResolver
    ) {
    }

    public function generateLocationBreadcrumb(Location $location): Breadcrumb
    {
        return Breadcrumb::createLazyGhost(function (Breadcrumb $instance) use ($location) {
            $rootLocationId = $this->configResolver->getParameter('content.tree_root.location_id');
            $currentLocation = $location;
            $breadcrumbLinks = [$this->linkGenerator->generateLocationLink($location)];

            if (in_array($rootLocationId, $location->path)) {
                do {
                    $parentLocation = $currentLocation->getParentLocation();
                    if ($parentLocation === null) {
                        break;
                    }
                    $breadcrumbLinks[] = $this->linkGenerator->generateLocationLink($parentLocation);
                    $currentLocation = $parentLocation;
                } while ($parentLocation->id !== $rootLocationId);
            }

            $breadcrumbLinks = array_reverse($breadcrumbLinks);
            foreach ($breadcrumbLinks as $link) {
                $instance->add($link);
            }
        });
    }
}
