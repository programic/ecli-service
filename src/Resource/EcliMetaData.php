<?php

namespace Programic\EcliService\Resource;

use \SimpleXMLElement;

class EcliMetaData
{
    protected $identifier = null;

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
}
