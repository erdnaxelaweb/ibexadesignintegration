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
use Exception;
use Ibexa\Contracts\Core\Persistence\Handler as PersistenceHandler;
use Ibexa\Contracts\Core\Search\Document as IbexaDocument;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType\BooleanField;
use Ibexa\Contracts\Core\Search\FieldType\DocumentField;
use Ibexa\Contracts\Core\Search\FieldType\IdentifierField;
use Ibexa\Contracts\Core\Search\FieldType\StringField;
use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;
use Ibexa\Solr\FieldMapper\ContentFieldMapper\BlockDocumentsBaseContentFields;
use Ibexa\Solr\FieldMapper\ContentFieldMapper\ContentDocumentLocationFields;
use Ibexa\Solr\FieldMapper\ContentTranslationFieldMapper\BlockDocumentsMetaFields;
use Novactive\EzSolrSearchExtra\Search\ExtendedSearchHandler;

class DocumentIndexer
{
    public function __construct(
        protected SearchFieldResolver             $searchFieldResolver,
        protected DocumentBuilder                 $documentBuilder,
        protected ExtendedSearchHandler           $extendedSearchHandler,
        protected DefinitionManager               $definitionManager,
        protected ContentDocumentLocationFields   $contentDocumentLocationFields,
        protected BlockDocumentsBaseContentFields $blockDocumentsBaseContentFields,
        protected BlockDocumentsMetaFields        $blockDocumentsMetaFields,
        protected PersistenceHandler              $persistenceHandler,
        protected PurgeClientInterface            $purgeClient
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

        $this->extendedSearchHandler->bulkIndexDocuments($indexableDocuments);
        $cacheTags = [];
        foreach ($documents as $document) {
            $cacheTags[] = $document->cacheTag();
            $cacheTags[] = sprintf('d-%s', $document->getShortType());
        }
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

    /**
     * @param string[]      $documentTypes
     * @param string[]|null $languageCodes
     */
    public function deleteContentDocuments(array $documentTypes, int $contentId, ?array $languageCodes = null): void
    {
        $documentIds = [];
        foreach ($documentTypes as $documentType) {
            if ($languageCodes) {
                foreach ($languageCodes as $key => $languageCode) {
                    $documentId = ($this->documentBuilder)->generateDocumentId(
                        $documentType,
                        $contentId,
                        $languageCode
                    );
                    $documentIds[] = $documentId;
                }
            } else {
                $documentId = ($this->documentBuilder)->generateDocumentId(
                    $documentType,
                    $contentId
                );
                $documentIds[] = $documentId;
            }
        }
        $this->extendedSearchHandler->deleteDocuments($documentIds);
    }

    protected function transformToIndexableDocument(Document $document): IbexaDocument
    {
        $documentId = $document->id;
        $fields = [];

        foreach ($document->fields as $fieldIdentifier => $value) {
            if ($value === null) {
                continue;
            }
            $resolvedField = $this->resolveFieldValue(
                $fieldIdentifier,
                $value
            );
            if ($resolvedField === null) {
                continue;
            }

            if ($resolvedField->getType() instanceof DocumentField) {
                $resolvedFieldValue = $resolvedField->getValue();
                $this->setNestedDocId(
                    $resolvedFieldValue,
                    sprintf('%s-%s', $documentId, $fieldIdentifier)
                );
                $resolvedField = new Field(
                    $resolvedField->getName(),
                    $resolvedFieldValue,
                    $resolvedField->getType(),
                );
            }

            $fields[$fieldIdentifier] = $resolvedField;
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
            'hidden',
            $document->hidden,
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
                'id' => $documentId,
                'languageCode' => $document->languageCode,
                'alwaysAvailable' => $document->alwaysAvailable,
                'isMainTranslation' => $document->isMainTranslation,
                'fields' => $fields,
            ]
        );
    }

    protected function setNestedDocId(mixed &$value, string $id): void
    {
        $id = preg_replace('([^A-Za-z0-9/]+)', '', $id);
        if (is_array($value)) {
            if (array_is_list($value)) {
                foreach ($value as $k => $v) {
                    $this->setNestedDocId($value[$k], sprintf('%s-%d', $id, $k));
                }
            } else {
                $value['id'] = $id;
            }
            return;
        } elseif (is_object($value)) {
            $value->id = $id;
            return;
        }

        throw new Exception('Unexpected value for a document field');
    }

    protected function resolveFieldValue(string $fieldIdentifier, mixed $value): ?Field
    {
        return ($this->searchFieldResolver)($fieldIdentifier, $value);
    }
}
