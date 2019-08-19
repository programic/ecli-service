<?php

namespace Programic\EcliService\Resource;

use \SimpleXMLElement;

class EcliMetaData
{
    protected $identifier = null;
    protected $modified = null;
    protected $issued = null;
    protected $publisher = null;
    protected $creator = null;
    protected $date = null;
    protected $type = null;
    protected $subject = null;
    protected $relation = [];
    protected $references = [];

    public function __construct(SimpleXMLElement $element)
    {
        $this->identifier = (string) $element->identifier;
        $this->modified = (string) $element->modified;
        $this->issued = (string) $element->issued;
        $this->publisher = (string) $element->publisher;
        $this->creator = (string) $element->creator;
        $this->date = (string) $element->date;
        $this->type = (string) $element->type;
        $this->subject = (string) $element->subject;
        $this->relation = (array) $element->relation;
        $this->references = (array) $element->references;

        return $this;
    }

    public static function create(SimpleXMLElement $element)
    {
        $instance = new self($element);

        return $instance;
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
            'creator' => $this->creator,
            'date' => $this->date,
            'type' => $this->type,
            'subject' => $this->subject,
            'relation' => $this->relation,
            'references' => $this->references,
        ];
    }
}
