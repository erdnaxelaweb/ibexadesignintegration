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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Helper;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Routing\RouterInterface;

class LinkGenerator
{
    public function __construct(
        protected FactoryInterface $factory,
        protected RouterInterface  $router
    ) {
    }

    public function generateLocationLink(Location $location): ItemInterface
    {
        return $this->generateLink(
            $this->router->generate(UrlAliasRouter::URL_ALIAS_ROUTE_NAME, [
                'locationId' => $location->id,
            ]),
            $location->getContent()
                ->getName()
        );
    }

    public function generateContentLink(Content $content): ItemInterface
    {
        return $this->generateLocationLink($content->contentInfo->getMainLocation());
    }

    public function generateLink(string $url, string $label, array $options): ItemInterface
    {
        $options['uri'] = $url;
        return $this->factory->createItem($label, $options);
    }
}
