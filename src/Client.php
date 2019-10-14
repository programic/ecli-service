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
    public function organizations(bool $onlyActive = true)
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
     * @return Resource\Jurisdiction
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
     * @return bool|Resource\EcliMetaData
     */
    public function getEcliMetaData(string $ecliNumber)
    {
        $body = $this->getXmlBody('content?id='. $ecliNumber . '&return=META');
        if ($body === false) {
            return false;
        }
        $namespaces = $body->getNamespaces(true);

        if (!empty($body->children($namespaces['rdf']))) {
            $rdf = $body->children($namespaces['rdf'])->RDF;
            $xmlDescription = $rdf->Description[0];
        }
        $metaData = $xmlDescription->children($namespaces['dcterms']);
        if (!empty($metaData)) {
            $resource = Resource\EcliMetaData::create($metaData);
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
}
