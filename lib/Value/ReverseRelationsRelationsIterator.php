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

use ArrayIterator;
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentRelationsIteratorInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\RelationList\RelationListItemInterface;

class ReverseRelationsRelationsIterator implements ContentRelationsIteratorInterface
{
    public const BATCH_SIZE = 25;

    /**
     * @var ArrayIterator<int, RelationListItemInterface>
     */
    protected ArrayIterator $iterator;
    protected int $count;
    protected int $position = 0;

    public function __construct(
        protected ContentService $contentService,
        protected ContentTransformer $contentTransformer,
        protected ContentInfo $contentInfo,
        protected int $batchSize = self::BATCH_SIZE,
    ) {
    }

    public function count(): int
    {
        if (!isset($this->count)) {
            $this->count = $this->contentService->countReverseRelations($this->contentInfo);
        }
        return $this->count;
    }

    public function current(): Content
    {
        /** @var RelationListItemInterface $relationListItem */
        $relationListItem = $this->getIterator()->current();
        return $this->contentTransformer->lazyTransformContentFromContentId(
            $relationListItem->getRelation()->getDestinationContentInfo()->id
        );
    }

    public function next(): void
    {
        ++$this->position;
        $this->getIterator()->next();
        if (!$this->getIterator()->valid() && $this->position < $this->count) {
            unset($this->iterator);
        }
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function valid(): bool
    {
        $this->getIterator()->valid();
    }

    public function rewind(): void
    {
        $this->position = 0;
        unset($this->iterator);
    }

    /**
     * @return ArrayIterator<int, RelationListItemInterface>
     */
    protected function getIterator(): ArrayIterator
    {
        if (!isset($this->iterator)) {
            $reverseRelationList = $this->contentService->loadReverseRelationList(
                $this->contentInfo,
                $this->position,
                $this->batchSize
            );
            $this->iterator = new ArrayIterator(
                $reverseRelationList->items
            );
            $this->count = $reverseRelationList->totalCount;
        }
        return $this->iterator;
    }
}
