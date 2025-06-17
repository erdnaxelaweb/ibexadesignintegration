<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Document;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\Content;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DocumentDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Document\DocumentBuilder;
use ErdnaxelaWeb\StaticFakeDesign\Value\Document;
use Ibexa\Contracts\Core\Persistence\Handler as PersistenceHandler;
use Ibexa\Contracts\Core\Search\Document as IbexaDocument;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType\BooleanField;
use Ibexa\Contracts\Core\Search\FieldType\IdentifierField;
use Ibexa\Contracts\Core\Search\FieldType\StringField;
use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;
use Ibexa\Solr\FieldMapper\ContentFieldMapper\BlockDocumentsBaseContentFields;
use Ibexa\Solr\FieldMapper\ContentFieldMapper\ContentDocumentLocationFields;
use Ibexa\Solr\FieldMapper\ContentTranslationFieldMapper\BlockDocumentsMetaFields;
use Novactive\EzSolrSearchExtra\Search\DocumentSearchHandler;

class DocumentIndexer
{
    public function __construct(
        protected SearchFieldResolver   $searchFieldResolver,
        protected DocumentBuilder       $documentBuilder,
        protected DocumentSearchHandler $documentSearchHandler,
        protected DefinitionManager     $definitionManager,
        protected ContentDocumentLocationFields $contentDocumentLocationFields,
        protected BlockDocumentsBaseContentFields $blockDocumentsBaseContentFields,
        protected BlockDocumentsMetaFields $blockDocumentsMetaFields,
        protected PersistenceHandler $persistenceHandler,
        protected PurgeClientInterface $purgeClient
    ) {
    }

    /**
     * @param Document[] $documents
     */
    public function __invoke(array $documents): void
    {
        $indexableDocuments = [];
        foreach ($documents as $document) {
            $indexableDocuments[] = $this->transformToIndexableDocument($document);
        }

        $this->documentSearchHandler->bulkIndexDocuments($indexableDocuments);
        $cacheTags = array_map(function (Document $document) {
            return sprintf('d-%s', $document->id);
        }, $documents);
        $this->purgeClient->purge($cacheTags);
    }

    /**
     * @param string[]                                             $documentTypes
     *
     * @throws \ErdnaxelaWeb\StaticFakeDesign\Exception\DefinitionNotFoundException
     * @throws \ErdnaxelaWeb\StaticFakeDesign\Exception\DefinitionTypeNotFoundException
     */
    public function indexContent(Content $content, array $documentTypes): void
    {
        $documents = [];
        foreach ($documentTypes as $documentType) {
            $configuration = $this->definitionManager->getDefinition(DocumentDefinition::class, $documentType);

            foreach ($content->languageCodes as $key => $languageCode) {
                $documents[] = ($this->documentBuilder)(
                    $documentType,
                    $content,
                    $configuration->getFields(),
                    $languageCode
                );
            }
        }
        $this->__invoke($documents);
    }

    protected function transformToIndexableDocument(Document $document): IbexaDocument
    {
        $fields = [];

        foreach ($document->fields as $fieldIdentifier => $value) {
            if ($value === null) {
                continue;
            }
            $resolvedValue = $this->resolveFieldValue(
                $fieldIdentifier,
                $value
            );
            if ($resolvedValue === null) {
                continue;
            }
            $fields[$fieldIdentifier] = $resolvedValue;
        }

        $fields[] = new Field(
            'document_type',
            'document',
            new IdentifierField()
        );
        $fields[] = new Field(
            'language_code',
            $document->languageCode,
            new StringField()
        );
        $fields[] = new Field(
            'is_main_translation',
            $document->isMainTranslation,
            new BooleanField()
        );
        $fields[] = new Field(
            'always_available',
            $document->alwaysAvailable,
            new BooleanField()
        );
        $fields[] = new Field(
            'type',
            $document->type,
            new StringField()
        );

        $contentPersistance = $this->persistenceHandler->contentHandler()->load($document->contentId);

        $additionalFields = array_merge(
            $this->contentDocumentLocationFields->mapFields($contentPersistance),
            $this->blockDocumentsBaseContentFields->mapFields($contentPersistance),
            $this->blockDocumentsMetaFields->mapFields(
                $contentPersistance,
                $document->languageCode
            )
        );

        $fields = array_merge($fields, $additionalFields);

        return new IbexaDocument(
            [
                'id' => $document->id,
                'languageCode' => $document->languageCode,
                'alwaysAvailable' => $document->alwaysAvailable,
                'isMainTranslation' => $document->isMainTranslation,
                'fields' => $fields,
            ]
        );
    }

    protected function resolveFieldValue(string $fieldIdentifier, mixed $value): ?Field
    {
        return ($this->searchFieldResolver)($fieldIdentifier, $value);
    }
}
