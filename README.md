# Programic - Ecli-service

Met deze package kun je informatie opvragen via de open API van Rechtspraak.nl

Elke call zal een resource of een array met resources teruggeven.

## Gebruik
Om deze package te gebruiken, installeer je de package via composer.

In je `composer.json`:
```json
{
  "require": {
    "programic/ecli-service": "^1.0"
  }
}
```

In je code:
```php
<?php

use Programic\EcliService\Client;

$client = new Client();
```

## Functies

### organizations
Returns an array with instances of Resource\Organization

if parameter `onlyActive` is set to false, also inactive organizations will be returned.

```php
$results = $client->organizations(true);
$organization = $results[0];

$organization->name;
$organization->type;
$organization->abbreviation;
$organization->identifier;
$organization->startDate;
$organization->endDate;
```

### jurisdictions
Returns an array with instances of Resource\Jurisdiction

```php
$results = $client->jurisdictions();
$jurisdiction = $results[0];

$jurisdiction->name;
$jurisdiction->identifier;
$jurisdiction->subJurisdictions;
```

### procedureTypes
Returns an array with instances of Resource\ProcedureType

```php
$results = $client->procedureTypes();
$procedureType = $results[0];

$procedureType->name;
$procedureType->identifier;
```

### getEcliMetaData
Returns an instance of Resource\EcliMetaData

```php
$ecliData = $client->getEcliMetaData('ECLI:NL:HR:2014:952');

$ecliData->identifier;
$ecliData->modified;
$ecliData->issued;
$ecliData->publisher;
$ecliData->creator;
$ecliData->date;
$ecliData->type;
$ecliData->subject;
$ecliData->relation;
$ecliData->references;
```

### ecliExists
Returns a boolean to determine if the provided ECLI-number exists

```php
$ecliExists = $client->ecliExists('ECLI:NL:HR:2014:952');
```
