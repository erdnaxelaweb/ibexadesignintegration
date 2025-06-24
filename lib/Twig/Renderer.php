<?php

declare(strict_types=1);

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
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Twig\Environment;

class Renderer extends BaseRenderer
{
    public function __construct(
        protected FragmentHandler $fragmentHandler,
        string                    $renderTemplate,
        DefinitionManager         $definitionManager
    ) {
        parent::__construct($renderTemplate, $definitionManager);
    }

    public function renderPager(
        Environment $environment,
        string      $id,
        string      $pagerType,
        array $apiParameters = [],
        array $appContext = [],
        array $additionalParameters = []
    ): ?string {
        $uri = new ControllerReference(
            PagerRenderController::class,
            [
                'id' => $id,
                'pagerType' => $pagerType,
                'apiParameters' => $apiParameters,
                'appContext' => $appContext,
                'additionalParameters' => $additionalParameters,
            ]
        );

        return $this->fragmentHandler->render($uri, 'esi');
    }

    protected function getDisplayFunctions(): array
    {
        $displayFunction = parent::getDisplayFunctions();
        $displayFunction['render_pager'] = [$this, 'renderPager'];
        return $displayFunction;
    }
}
