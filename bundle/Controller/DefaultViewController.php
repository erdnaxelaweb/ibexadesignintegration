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

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\Controller;


use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use Ibexa\Core\MVC\Symfony\Controller\Controller;
use Ibexa\Core\MVC\Symfony\View\ContentView;

class DefaultViewController extends Controller
{
    public function __construct(
        protected ContentTransformer $contentTransformer
    )
    {
    }

    public function viewAction( ContentView $view )
    {
        $content = $view->getContent();
        $location = $view->getLocation();

        $contentDecorator = ( $this->contentTransformer )( $content, $location );


        return $view;
    }
}
