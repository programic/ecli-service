<?php

namespace Programic\EcliService\Resource;

use \SimpleXMLElement;

class EcliMetaData
{
    protected $identifier = null;
    protected $modified = null;
    protected $issued = null;
    protected $publisher = null;
    protected $language = null;
    protected $creator = null;
    protected $date = null;
    protected $type = null;
    protected $spatial = null;
    protected $subject = null;
    protected $relation = [];
    protected $references = [];
    protected $decision = [];
    protected $verdict = [];

    public function __construct(SimpleXMLElement $element, $decision, $verdict)
    {
        $this->identifier = (string) $element->identifier;
        $this->modified = (string) $element->modified;
        $this->issued = (string) $element->issued;
        $this->publisher = (string) $element->publisher;
        $this->language = (string) $element->language;
        $this->creator = (string) $element->creator;
        $this->date = (string) $element->date;
        $this->type = (string) $element->type;
        $this->spatial = (string) $element->spatial;
        $this->subject = (string) $element->subject;
        $this->relation = (array) $element->relation;
        $this->references = (array) $element->references;
        $this->decision = (array) $decision;
        $this->verdict = (array) $verdict;

        return $this;
    }

    public static function create(SimpleXMLElement $element, $decision, $verdict)
    {
        return new self($element, $decision, $verdict);
    }

    public function __get($name)
    {
        return $this->{$name};
    }

    public function toArray()
    {
        return [
            'identifier' => $this->identifier,
            'modified' => $this->modified,
            'issued' => $this->issued,
            'publisher' => $this->publisher,
            'language' => $this->language,
            'creator' => $this->creator,
            'date' => $this->date,
            'type' => $this->type,
            'spatial' => $this->spatial,
            'subject' => $this->subject,
            'relation' => $this->relation,
            'references' => $this->references,
            'decision' => $this->decision,
            'verdict' => $this->verdict,
        ];
    }
}
