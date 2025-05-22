<?php

declare( strict_types=1 );

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Twig;

use ErdnaxelaWeb\IbexaDesignIntegrationBundle\Controller\PagerRenderController;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Templating\Twig\Renderer as BaseRenderer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\TwigFunction;

class Renderer extends BaseRenderer
{
    public function __construct(
        protected FragmentHandler $fragmentHandler,
        protected RequestStack    $requestStack,
        protected RouterInterface $router,
        protected Esi $esi,
        string                    $renderTemplate,
        DefinitionManager         $definitionManager
    )
    {
        parent::__construct( $renderTemplate, $definitionManager );
    }

    protected function getDisplayFunctions(): array
    {
        $displayFunction = parent::getDisplayFunctions();
        $displayFunction['render_pager'] = [ $this, 'renderPager' ];
        return $displayFunction;
    }

    public function renderPager(
        Environment $environment,
        string      $pagerType
    ): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        $uri = $request->getScheme() . '://' . $request->getHost() . ':5174/init/' . $pagerType;
        $uri .= '?' . http_build_query(
                [
                    'api_url' => $this->router->generate(
                        'ibexa_design_integration.api.pager',
                        [ 'type' => $pagerType ],
                        Router::ABSOLUTE_URL
                    ),
                    'url' => $request->getUri(),
                ]
            );
        dump( $uri );

        if (!$this->esi || !$this->esi->hasSurrogateCapability($request)) {
            $uri = new ControllerReference(
                PagerRenderController::class,
                [ 'uri' => $uri ]
            );
        }

        return $this->fragmentHandler->render( $uri, 'esi' );
    }
}
