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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Migration;

use ErdnaxelaWeb\StaticFakeDesign\Configuration\ContentConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\TaxonomyEntryConfigurationManager;

class MigrationGenerator
{
    public function __construct(
        protected ContentConfigurationManager $contentConfigurationManager,
        protected TaxonomyEntryConfigurationManager $taxonomyEntryConfigurationManager
    )
    {
    }


    public function generate(  )
    {
        throw new \Exception('not implemented');
    }
}
