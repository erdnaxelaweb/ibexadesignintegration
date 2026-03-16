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
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentFieldsCollection;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaApiContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as IbexaApiLocation;
use Ibexa\Contracts\Taxonomy\Value\TaxonomyEntry as IbexaTaxonomyEntry;

/**
 * @property-read IbexaTaxonomyEntry $innerTaxonomy
 * @property-read string $identifier
 * @property-read bool                    $hidden
 * @property-read int $level
 * @property-read ?TaxonomyEntry $parent
 */
class TaxonomyEntry extends AbstractContent
{
    public function __construct(
        IbexaApiContent $innerContent,
        ?IbexaApiLocation $innerLocation,
        int $id,
        ?int $locationId,
        string $name,
        string $type,
        ?DateTime $creationDate,
        ?DateTime $modificationDate,
        ContentFieldsCollection $fields,
        public readonly IbexaTaxonomyEntry $innerTaxonomy,
        public readonly string $identifier,
        public readonly bool                    $hidden,
        public readonly int $level = 0,
        public readonly ?TaxonomyEntry $parent = null,
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

    public function getInnerTaxonomy(): IbexaTaxonomyEntry
    {
        return $this->getPropertyValue('innerTaxonomy');
    }

    public function getIdentifier(): string
    {
        return $this->getPropertyValue('identifier');
    }

    public function isHidden(): bool
    {
        return $this->getPropertyValue('hidden');
    }

    public function getLevel(): int
    {
        return $this->getPropertyValue('level');
    }

    public function getParent(): ?TaxonomyEntry
    {
        return $this->getPropertyValue('parent');
    }
}
