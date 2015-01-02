
# API SDK

This SDK has been developed to help commercial partners to integrate in there web applications some API calls for remote registration.
At the moment, this SDK has been only developed for PHP websites purpose. SDK for other languages should be developed in the near future.

# Lead document
Here is a sample Lead document :
```json
{
    "email": "valid-email@domain.example.com",
    "birthdate": "1950-01-01T06:00:00.000Z",
    "gender": null,
    "firstName": null,
    "lastName": null,
    "originDetail": {
        "referer": null,
        "subid": null,
        "theme": null,
        "campaign": null,
        "campaignarea": null
    },
    "location":{
        "registrationIpV4": null,
        "registrationIpV6": null,
        "number": null,
        "street": null,
        "street2": null,
        "street3": null,
        "zip": null,
        "city": null,
        "state": null,
        "country": "NZL"
    }
}
```
The required properties are :
* `email`
* `birthdate` (ISO8601)
* `gender` (male/female)
* either `firstName` or `lastName`
* `location.country` which is a valid and NOT blacklisted ISO3 country

# Example
## PHP
### config/api.ini
```ini
login="Sample"
password="S3cretKey!!!"
url="https://api.example.com/api/"
```
### file.php
```php
<?php
require_once(dirname(__FILE__).'/class/lead-generation.class.php');
$config = parse_ini_file('config/api.ini', true);

$lead = [
];

try {
    $api = new RemoteApi\APIConnection(
        $config['resources']['login'],
        $config['resources']['password']
    );
    $api->setApiUrl($config['resources']['url']);
    $api->connect();
    /*
     * From here you can use a loop to post multiple leads
     * */
    $api->push($lead);
} catch (Exception $e) {
    // Do something with the exception
}
```
## JS

## Java
 
## Python
