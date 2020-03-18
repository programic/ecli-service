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
    protected $source = null;

    public function __construct(SimpleXMLElement $element, $decision, $verdict, $source)
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
        $this->source = $source;

        return $this;
    }

    public static function create(SimpleXMLElement $element, $decision, $verdict, $source)
    {
        return new self($element, $decision, $verdict, $source);
    }

    public function __get($name)
    {
        return $this->{$name};
    }

    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    public function toArray()
    {
        $data = [
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

        if ($this->source) {
            $data['source'] = $this->source;
        }

        return $data;
    }
}
