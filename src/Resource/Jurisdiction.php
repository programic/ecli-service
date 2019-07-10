<?php

namespace Programic\EcliService\Resource;

use \SimpleXMLElement;

class Jurisdiction
{
    protected $name = null;
    protected $identifier = null;
    protected $subJurisdictions = [];

    public function __construct(SimpleXMLElement $element)
    {
        $this->name = (string) $element->Naam;
        $this->identifier = (string) $element->Identifier;
        if (isset($element->Rechtsgebied)) {
            foreach ($element->Rechtsgebied as $subItem) {
                $this->subJurisdictions[] = new self($subItem);
            }
        }

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
