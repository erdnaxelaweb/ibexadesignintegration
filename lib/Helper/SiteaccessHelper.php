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

use Ibexa\AdminUi\Siteaccess\SiteaccessResolverInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\Event\ScopeChangeEvent;
use Ibexa\Core\MVC\Symfony\MVCEvents;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessRouterInterface;
use Ibexa\Core\MVC\Symfony\Templating\GlobalHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SiteaccessHelper
{
    protected SiteAccess $originalSiteAccess;

    public function __construct(
        protected SiteAccessRouterInterface $siteAccessRouter,
        protected SiteaccessResolverInterface $siteAccessResolver,
        protected GlobalHelper $globalHelper,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getSiteAccesseForLocation(Location $location, string $languageCode = null): ?SiteAccess
    {
        if (
            $location->isDraft() ||
            in_array($this->globalHelper->getRootLocation()->id, $location->path, true)
        ) {
            return null;
        }

        $siteAccesses = $this->siteAccessResolver->getSiteAccessesListForLocation($location, null, $languageCode);
        $siteAccesses = array_filter($siteAccesses, function (SiteAccess $siteAccess) {
            return $siteAccess->name !== 'corporate';
        });
        return !empty($siteAccesses) ? reset($siteAccesses) : null;
    }



    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        $this->originalSiteAccess = $siteAccess;
    }

    /**
     * Return original SiteAccess.
     *
     * @return SiteAccess
     */
    public function getOriginalSiteAccess()
    {
        return $this->originalSiteAccess;
    }

    /**
     * Switches configuration scope to $siteAccessName and returns the new SiteAccess to use for preview.
     *
     * @param string $siteAccessName
     *
     * @return SiteAccess
     */
    public function changeConfigScope($siteAccessName)
    {
        $event = new ScopeChangeEvent($this->siteAccessRouter->matchByName($siteAccessName));
        $this->eventDispatcher->dispatch($event, MVCEvents::CONFIG_SCOPE_CHANGE);

        return $event->getSiteAccess();
    }

    /**
     * Restores original config scope.
     *
     * @return SiteAccess
     */
    public function restoreConfigScope()
    {
        $event = new ScopeChangeEvent($this->originalSiteAccess);
        $this->eventDispatcher->dispatch($event, MVCEvents::CONFIG_SCOPE_RESTORE);

        return $event->getSiteAccess();
    }
}
