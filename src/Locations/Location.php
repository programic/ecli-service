<?php

namespace Programic\EcliService\Locations;

use GuzzleHttp\Client;
use Programic\EcliService\Locations\Exceptions\LocationErrorException;
use Programic\EcliService\Locations\Exceptions\LocationValidationException;

/**
 * Class Location
 * @package Programic\EcliService\Locations
 *
 * @method sync(array $data)
 * @method add(array $data)
 * @method edit(array $data)
 * @method delete(array $data)
 */
class Location
{
    private $client;
    private $credentials;

    protected $baseUrl;
    protected $fields = [
        'ecli',
        'location',
        'articleid',
        'url'
    ];

    /**
     * Location constructor.
     * @throws LocationErrorException
     */
    public function __construct()
    {
        $config = [
            'base_uri' => $this->baseUrl,
            'defaults' => [
                'headers' => [
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Accept' => 'text/xml',
                ]
            ],
            'timeout' => 2.0,
        ];

        $this->client = new Client($config);

        $this->credentials();

        return $this;
    }

    public static function __callStatic($method, $arguments)
    {
        $class = new Location;

        $method = $method.'Soap';
        if (method_exists($class, $method)) {
            $class->{$method}($arguments[0]);
        }

        throw new LocationErrorException('Static call unknown');
    }

    public function __call($method, $arguments)
    {
        $method = $method.'Soap';
        if (method_exists($this, $method)) {
            $this->{$method}($arguments[0]);
        }

        throw new LocationErrorException('Method not found');
    }

    /**
     * @param array $data
     * @return bool
     * @throws LocationErrorException
     */
    private function syncSoap(array $data)
    {
        if ($result = $this->submitVindplaatsRechtSpraak("Add", $data)) {
            switch ($result['type']) {
                case "error":
                    // On error, maybe the location already exists, so we try to update
                    if ($result1 = $this->submitVindplaatsRechtSpraak("Edit", $data)) {
                        switch ($result1['type']) {
                            case "error":
                                throw new LocationErrorException($result1['message']);
                                break;
                            case "info":
                                return true;
                                break;
                            default:
                                throw new LocationErrorException("Unknown error");
                                break;
                        }
                    }
                    break;

                case "info":
                    return true;
                    break;
                default:
                    throw new LocationErrorException("Unknown error");
                    break;
            }
        } else {
            throw new LocationErrorException("Unknown error");
        }

        return true;
    }

    private function addSoap(array $data)
    {
        return $this->submitVindplaatsRechtSpraak("Add", $data);
    }

    private function editSoap(array $data)
    {
        return $this->submitVindplaatsRechtSpraak("Edit", $data);
    }

    private function deleteSoap(array $data)
    {
        return $this->submitVindplaatsRechtSpraak("Delete", $data);
    }

    private function submitVindplaatsRechtSpraak($type = "Add", $data = [])
    {
        $data = $this->validate($data);

        $service = $this->baseUrl."/Vindplaats.svc";

        switch ($type) {
            case "Add":
                $endpoint = "http://www.rechtspraak.nl/npi/service/ecli/vindplaatsservice/Aanmelden";
                $action = "Aanmelden";
                break;
            case "Edit":
                $endpoint = "http://www.rechtspraak.nl/npi/service/ecli/vindplaatsservice/Wijzigen";
                $action = "Wijzigen";
                break;
            case "Delete":
                $endpoint = "http://www.rechtspraak.nl/npi/service/ecli/vindplaatsservice/Verwijderen";
                $action = "Verwijderen";
                break;
            default:
                throw new LocationErrorException("Action " . $type . " unknown");
                break;
        }

        $date = date("Y-m-d");
        $base64rand = base64_encode(sha1(mt_rand()));
        if (is_array($data['auteurs']) === false) {
            $data['auteurs'] = [$data['auteurs']];
        }
        $data['auteurs'] = array_filter($data['auteurs']);
        foreach ($data['auteurs'] as $key => $name) {
            $data['auteurs'][$key] = "<vin:Naam><![CDATA[" . $name . "]]></vin:Naam>";
        }
        $authorsSoap = implode("\n", $data['auteurs']);

        $soapData = "<?xml version=\"1.0\"?>
<soapenv:Envelope
    xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\"
    xmlns:vin=\"http://www.rechtspraak.nl/npi/service/ecli/vindplaatsservice\">
<soapenv:Header>
    <wsse:Security
        soapenv:mustUnderstand=\"1\"
        xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">
      <wsse:UsernameToken
        wsu:Id=\"UsernameToken-1\"
        xmlns:wsu=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd\">
        <wsse:Username>" . $this->credentials['username'] ."</wsse:Username>
        <wsse:Password
            Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">
            " . $this->credentials['password'] . "
        </wsse:Password>
        <wsse:Nonce
            EncodingType=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary\">
                " . $base64rand . "
        </wsse:Nonce>
        <wsu:Created>" . $date . "</wsu:Created>
      </wsse:UsernameToken>
    </wsse:Security>
  </soapenv:Header>
   <soapenv:Body>
       <vin:" . $action . ">
         <vin:request>
            <vin:Vindplaats>" . $data['location'] . "</vin:Vindplaats>
            <vin:Ecli>" . $data['ecli'] . "</vin:Ecli>
            <vin:Annotators>
               <vin:Annotator>
                  " . $authorsSoap . "
               </vin:Annotator>
            </vin:Annotators>
            <vin:Url>" . $data['url'] . "</vin:Url>
         </vin:request>
      </vin:" . $action . ">
   </soapenv:Body>
</soapenv:Envelope>";

        if ($result = $this->sendSoapRequest($service, $endpoint, $soapData)) {
            return $this->checkRechtspraakResponse($result);
        }

        throw new LocationErrorException("403 Forbidden");
    }

    private function sendSoapRequest($service, $action, $soapData, $timeout = 10)
    {
        $header = [
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"" . $action . "\"",
            "Content-length: " . strlen($soapData),
        ];

        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $service);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($soap_do, CURLOPT_VERBOSE, true);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST, true);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, $soapData);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($soap_do);
        curl_close($soap_do);


        return $result;
    }


    private function checkRechtspraakResponse($xml)
    {
        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xml); // adding this worked for me
        $array = json_decode(json_encode((array) simplexml_load_string($xml)), true);

        if (isset($array['sBody']['sFault'])) {
            $response = [
                'type' => 'error',
                'message' => $array['sBody']['sFault']['faultstring']
            ];
        } elseif (isset($array['sBody']['AanmeldenResponse'])) {
            $response = [
                'type' => 'info',
                'message'     => $array['sBody']['AanmeldenResponse']['AanmeldenResult']
            ];
        } elseif (isset($array['sBody']['WijzigenResponse'])) {
            $response = [
                'type' => 'info',
                'message'     => $array['sBody']['WijzigenResponse']['WijzigenResult']
            ];
        } elseif (isset($array['sBody']['VerwijderenResponse'])) {
            $response = [
                'type' => 'info',
                'message' => $array['sBody']['VerwijderenResponse']['VerwijderenResult']
            ];
        } else {
            $response = [
                'type'  => 'unknown',
                'message' => serialize($array)
            ];
        }

        return $response;
    }

    private function credentials()
    {
        $base_uri = config('services.ecli.vindplaats.base_uri');
        $username = config('services.ecli.vindplaats.username');
        $password = config('services.ecli.vindplaats.password');

        if (! $base_uri) {
            throw new LocationErrorException('Ecli vindplaats base_uri not set');
        }

        if (! $username) {
            throw new LocationErrorException('Ecli vindplaats username not set');
        }

        if (! $password) {
            throw new LocationErrorException('Ecli vindplaats password not set');
        }

        $this->baseUrl = $base_uri;
        $this->credentials['username'] = $username;
        $this->credentials['password'] = $password;
    }

    private function validate(array $data) : array
    {
        foreach ($this->fields as $field) {
            if (empty($data[$field])) {
                throw new LocationValidationException('Field "' . $field . '" is required');
            }
        }

        return $data;
    }
}
