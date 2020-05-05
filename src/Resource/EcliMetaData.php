<?php

namespace Programic\EcliService\Resource;

class EcliMetaData
{
    protected $data = [];
    protected $source = null;

    public function __construct(array $data, $source)
    {
        $this->data = (array) $data;
        $this->data['source'] = $source;
        $this->data['creatorAbbreviation'] = $this->getEcliOrganizationType($this->identifier);

        return $this;
    }

    public static function create(array $element, $source)
    {
        return new self($element, $source);
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function toArray()
    {
        return $this->data;
    }

    private function getEcliOrganizationType($ecliValue)
    {
        if (strpos($ecliValue, 'ECLI:NL:') === false) {
            return null;
        }

        return substr($ecliValue, 8, 2);
    }
}
