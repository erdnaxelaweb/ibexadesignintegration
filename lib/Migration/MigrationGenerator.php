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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Migration;

use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;

class MigrationGenerator
{
    public function __construct(
        protected DefinitionManager $definitionManager
    ) {
    }

    public function generate()
    {
        throw new \Exception('not implemented');
    }
}
