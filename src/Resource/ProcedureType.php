<?php

namespace Programic\EcliService\Resource;

use \SimpleXMLElement;

class ProcedureType
{
    protected $name = null;
    protected $identifier = null;

    public function __construct(SimpleXMLElement $element)
    {
        $this->name = (string) $element->Naam;
        $this->identifier = (string) $element->Identifier;

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
            'name' => $this->name,
            'identifier' => $this->identifier,
        ];
    }
}
