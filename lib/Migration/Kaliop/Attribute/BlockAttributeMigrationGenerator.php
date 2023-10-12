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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Migration\Kaliop\Attribute;

use Exception;

class BlockAttributeMigrationGenerator implements AttributeMigrationGeneratorInterface
{
    public function generate(string $fieldIdentifier, array $field): array
    {
        throw new Exception('not implemented');
    }
}
