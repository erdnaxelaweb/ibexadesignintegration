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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use DateTime;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentFieldsCollection;
use ErdnaxelaWeb\StaticFakeDesign\Value\TaxonomyEntry as BaseTaxonomyEntry;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaApiContent;
use Symfony\Component\VarExporter\LazyGhostTrait;

class TaxonomyEntry extends BaseTaxonomyEntry
{
    use LazyGhostTrait;

    public function __construct(
        int                     $id,
        string                  $name,
        string                  $type,
        DateTime                $creationDate,
        DateTime                $modificationDate,
        ContentFieldsCollection $fields,
        public readonly IbexaApiContent $innerContent,
    ) {
        parent::__construct($id, $name, $type, $creationDate, $modificationDate, $fields);
    }
}
