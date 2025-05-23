<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\Controller;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use Ibexa\Core\MVC\Symfony\Controller\Controller;
use Ibexa\Core\MVC\Symfony\View\ContentView;

class DefaultViewController extends Controller
{
    public function __construct(
        protected ContentTransformer $contentTransformer,
        protected PagerBuilder $pagerBuilder
    ) {
    }

    public function viewAction(ContentView $view): ContentView
    {
        $content = $view->getContent();
        $location = $view->getLocation();

        $contentDecorator = ($this->contentTransformer)($content, $location);

        if ($view->hasParameter('pagerType')) {
            $pagerType = $view->getParameter('pagerType');
            $view->addParameters([
                'pager' => $this->pagerBuilder->build($pagerType, [
                    'location' => $location,
                ]),
            ]);
        }

        $view->setContent($contentDecorator);

        return $view;
    }
}
