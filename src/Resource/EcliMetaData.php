<?php

namespace Programic\EcliService\Resource;

class EcliMetaData
{
    protected $data = [];
    protected $source = null;

    public function __construct(array $data = [], $source = null)
    {
        if (isset($data['zaaknummer'])) {
            $data['caseNumber'] = $data['zaaknummer'];
            unset($data['zaaknummer']);
        }

        $this->data = (array) $data;
        $this->data['source'] = $source;
        $this->data['creatorAbbreviation'] = $this->getEcliOrganizationType($this->identifier);

        return $this;
    }

    public static function create(array $element, $source)
    {
        return new self($element, $source);
    }

    public static function fromCache(array $data)
    {
        $instance = new self;
        $instance->data = $data;

        return $instance;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function toArray()
    {
        return $this->toArrayRecursive($this->data);
    }

    private function toArrayRecursive($data)
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $this->toArrayRecursive($value);
            }

            return (string) $value;
        }, $data);
    }

    private function getEcliOrganizationType($ecliValue)
    {
        if (strpos($ecliValue, 'ECLI:NL:') !== 0) {
            return null;
        }

        return substr($ecliValue, 8, 2);
    }
}
