<?php

namespace Programic\EcliService;

use GuzzleHttp\Client as GuzzleClient;

class Client
{
    protected $client = null;
    protected $baseUrl = 'https://data.rechtspraak.nl/uitspraken/';

    /**
     * Client constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $baseConfig = [
            'base_uri' => $this->baseUrl,
            'timeout' => 2.0,
        ];

        $finalConfig = array_merge($baseConfig, $config);

        $this->client = new GuzzleClient($finalConfig);

        return $this;
    }

    /**
     * Fetch all organizations (Instanties)
     * @param bool $onlyActive
     * @return array
     */
    public function organizations($onlyActive = true)
    {
        $body = $this->getXmlBody('/Waardelijst/Instanties');
        $resultSet = [];

        if (!empty($body) && !empty($body->Instantie)) {
            foreach ($body->Instantie as $item) {
                $organization = Resource\Organization::create($item);
                if ($onlyActive && is_null($organization->endDate) === false) {
                    continue;
                }
                $resultSet[] = $organization;
            }
        }

        return $resultSet;
    }

    /**
     * Fetch all jurisdictions (Rechtsgebieden)
     * @return array
     */
    public function jurisdictions()
    {
        $body = $this->getXmlBody('/Waardelijst/Rechtsgebieden');
        $resultSet = [];

        if (!empty($body) && !empty($body->Rechtsgebied)) {
            foreach ($body->Rechtsgebied as $item) {
                $jurisdiction = Resource\Jurisdiction::create($item);
                $resultSet[] = $jurisdiction;
            }
        }

        return $resultSet;
    }

    /**
     * Fetch all procedureTypes (Procedure soorten)
     * @return Resource\ProcedureType[]
     */
    public function procedureTypes()
    {
        $body = $this->getXmlBody('/Waardelijst/Proceduresoorten');
        $resultSet = [];

        if (!empty($body) && !empty($body->Proceduresoort)) {
            foreach ($body->Proceduresoort as $item) {
                $procedureType = Resource\ProcedureType::create($item);
                $resultSet[] = $procedureType;
            }
        }

        return $resultSet;
    }

    /**
     * Get the metadata from an ECLI-number
     * @param string $ecliNumber
     * @param bool $metaOnly
     * @return bool|Resource\EcliMetaData
     */
    public function getEcliMetaData(string $ecliNumber, $metaOnly = false)
    {
        $metaParams = ($metaOnly) ? '&return=META' : '';
        $body = $this->getXmlBody('content?id='. $ecliNumber . $metaParams);
        if ($body === false) {
            return false;
        }
        $namespaces = $body->getNamespaces(true);

        if (!empty($body->children($namespaces['rdf']))) {
            $rdf = $body->children($namespaces['rdf'])->RDF;
            $xmlDescription = $rdf->Description[0];
        }
        $xmlData = $xmlDescription->children($namespaces['dcterms']);

        $verdicts = $body->children("http://www.rechtspraak.nl/schema/rechtspraak-1.0");

        $decision = [];
        if ($verdicts->inhoudsindicatie) {
            $decision = $this->xmlObjToArr($verdicts->inhoudsindicatie);
        }

        $verdict = [];
        if ($verdicts->uitspraak) {
            $verdict = $this->xmlObjToArr($verdicts->uitspraak);
        }

        if (!empty($xmlData)) {
            $resource = Resource\EcliMetaData::create($xmlData, $decision, $verdict);
        } else {
            $resource = false;
        }

        return $resource;
    }

    /**
     * Verify if an ECLI-number is valid
     * @param string $ecliNumber
     * @return bool
     */
    public function ecliExists(string $ecliNumber)
    {
        return $this->getEcliMetaData($ecliNumber) !== false;
    }

    /**
     * @param string $uri
     * @return bool|\SimpleXMLElement
     */
    protected function getXmlBody(string $uri)
    {
        $response = $this->client->get($uri);

        if ($response->getStatusCode() !== 200 || $response->getHeaderLine('Content-Type') === 'text/html; charset=utf-8') {
            return false;
        }

        $body = simplexml_load_string($response->getBody());

        return $body;
    }

    public function xmlObjToArr($obj) {
        $namespace = $obj->getDocNamespaces(true);
        $namespace[NULL] = NULL;

        $children = array();
        $attributes = array();
        $name = strtolower((string)$obj->getName());

        $text = trim((string)$obj);
        if( strlen($text) <= 0 ) {
            $text = NULL;
        }

        // get info for all namespaces
        if(is_object($obj)) {
            foreach( $namespace as $ns=>$nsUrl ) {
                // atributes
                $objAttributes = $obj->attributes($ns, true);
                foreach( $objAttributes as $attributeName => $attributeValue ) {
                    $attribName = strtolower(trim((string)$attributeName));
                    $attribVal = trim((string)$attributeValue);
                    if (!empty($ns)) {
                        $attribName = $ns . ':' . $attribName;
                    }
                    $attributes[$attribName] = $attribVal;
                }

                // children
                $objChildren = $obj->children($ns, true);
                foreach( $objChildren as $childName=>$child ) {
                    $childName = strtolower((string)$childName);
                    if( !empty($ns) ) {
                        $childName = $ns.':'.$childName;
                    }
                    $children[$childName][] = $this->xmlObjToArr($child);
                }
            }
        }

        return [
            'name' => $name,
            'text' => $text,
            'attributes' => $attributes,
            'children' => $children,
        ];
    }

}
