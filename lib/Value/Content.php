<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use DateTime;
use ErdnaxelaWeb\StaticFakeDesign\Value\Breadcrumb;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentFieldsCollection;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentRelationsIteratorInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaApiContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as IbexaApiLocation;

class Content extends AbstractContent
{
    public function __construct(
        IbexaApiContent $innerContent,
        ?IbexaApiLocation                        $innerLocation,
        int                                      $id,
        ?int                                     $locationId,
        string                                   $name,
        string                                   $type,
        ?DateTime                                $creationDate,
        ?DateTime                                $modificationDate,
        ContentFieldsCollection                  $fields,
        public readonly string                   $url,
        public readonly Breadcrumb               $breadcrumb,
        public ContentRelationsIteratorInterface $reverseRelations,
    ) {
        parent::__construct(
            $innerContent,
            $innerLocation,
            $id,
            $locationId,
            $name,
            $type,
            $creationDate,
            $modificationDate,
            $fields
        );
    }
}
