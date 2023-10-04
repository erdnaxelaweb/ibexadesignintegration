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

declare( strict_types=1 );

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\Controller;


use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use Ibexa\Core\MVC\Symfony\Controller\Controller;
use Ibexa\Core\MVC\Symfony\View\ContentView;

class DefaultViewController extends Controller
{
    public function __construct(
        protected ContentTransformer $contentTransformer,
        protected PagerBuilder       $pagerBuilder
    )
    {
    }

    public function viewAction( ContentView $view )
    {
        $content = $view->getContent();
        $location = $view->getLocation();

        $contentDecorator = ( $this->contentTransformer )( $content, $location );


        if ( $view->hasParameter( 'pagerType' ) )
        {
            $pagerType = $view->getParameter( 'pagerType' );
            $view->addParameters(
                [
                    'pager' => $this->pagerBuilder->build( $location, $pagerType )
                ]
            );
        }

        $view->setContent( $contentDecorator );

        return $view;
    }
}
