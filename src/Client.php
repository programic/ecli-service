<?php

namespace Programic\EcliService;

use GuzzleHttp\Client as GuzzleClient;


class Client
{
    protected $client = null;
    protected $baseUrl = 'https://data.rechtspraak.nl/uitspraken/';

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

    public function organizations()
    {
        $body = $this->getXmlBody('/Waardelijst/Instanties');
        $resultSet = [];

        foreach ($body->Instantie as $item) {
            $organization = Resource\Organization::create($item);
            $resultSet[] = $organization;
        }

        return $resultSet;
    }

    public function jurisdictions()
    {
        $body = $this->getXmlBody('/Waardelijst/Rechtsgebieden');
        $resultSet = [];

        foreach ($body->Rechtsgebied as $item) {
            $jurisdiction = Resource\Jurisdiction::create($item);
            $resultSet[] = $jurisdiction;
        }

        return $resultSet;
    }

    public function procedureTypes()
    {
        $body = $this->getXmlBody('/Waardelijst/Proceduresoorten');
        $resultSet = [];

        foreach ($body->Proceduresoort as $item) {
            $procedureType = Resource\ProcedureType::create($item);
            $resultSet[] = $procedureType;
        }

        return $resultSet;
    }

    public function getEcliMetaData(string $ecliNumber)
    {
        $body = $this->getXmlBody('content?id='. $ecliNumber . '&return=META');
        $namespaces = $body->getNamespaces(true);

        if (!empty($body->children($namespaces['rdf']))) {
            $rdf = $body->children($namespaces['rdf'])->RDF;
            $xmlDescription = $rdf->Description[0];

        }
        $metaData = $xmlDescription->children($namespaces['dcterms']);
        $resource = Resource\EcliMetaData::create($metaData);

        return $resource;
    }

    protected function getXmlBody(string $uri)
    {
        $response = $this->client->get($uri);

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $body = simplexml_load_string($response->getBody());

        return $body;
    }
}
