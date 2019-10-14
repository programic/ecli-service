<?php

namespace Programic\EcliService\Resource;

use \SimpleXMLElement;

class Organization
{
    protected $name = null;
    protected $type = null;
    protected $abbreviation = null;
    protected $identifier = null;
    protected $startDate = null;
    protected $endDate = null;

    public function __construct(SimpleXMLElement $element)
    {
        $this->name = (string) $element->Naam;
        $this->type = (string) $element->Type;
        $this->abbreviation = (string) $element->Afkorting;
        $this->identifier = (string) $element->Identifier;
        $this->startDate = (string) $element->BeginDate;
        if (isset($element->EndDate)) {
            $this->endDate = (string) $element->EndDate;
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

    public function toArray()
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'abbreviation' => $this->abbreviation,
            'identifier' => $this->identifier,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];
    }
}
