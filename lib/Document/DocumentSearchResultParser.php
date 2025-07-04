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

use ErdnaxelaWeb\IbexaDesignIntegration\Event\ParseDocumentResultEvent;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DocumentDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Value\Document;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DocumentSearchResultParser
{
    public function __construct(
        protected DefinitionManager $definitionManager,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function __invoke(object $object): Document
    {
        $document = new Document();
        $document->id = $object->id;
        $document->contentId = (int) $object->content_id_id;
        $document->languageCode = $object->language_code_s;
        $document->isMainTranslation = $object->is_main_translation_b;
        $document->alwaysAvailable = $object->always_available_b;
        $document->type = $object->type_s;
        $document->hidden = $object->hidden_b ?? false;

        $documentDefinition = $this->definitionManager->getDefinition(
            DocumentDefinition::class,
            $document->type
        );
        foreach ($documentDefinition->getFields() as $field => $value) {
            if (!property_exists($object, $field)) {
                continue;
            }

            $value = $object->$field;

            // Check if the string is a serialized object
            if (is_string($value) && preg_match('/^(a|s|i|d|b|N|O|C):/', $value)) {
                $value = unserialize($value);
            }

            if (str_ends_with($field, '_dt')) {
                $date = \Datetime::createFromFormat('Y-m-d\\TH:i:s\\Z', $value);
                $value = $date;
            }

            if (str_ends_with($field, '_gl')) {
                $location = explode(',', $value);
                $value = [
                    'latitude' => (float) $location[0],
                    'longitude' => (float) $location[1],
                ];
            }

            $document->fields->{$field} = $value;
        }

        $event = new ParseDocumentResultEvent(
            $object,
            $document,
        );

        $this->eventDispatcher->dispatch($event, ParseDocumentResultEvent::ON_PARSE);

        return $document;
    }
}
