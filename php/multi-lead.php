<?php
/**
 * User: charles
 * Date: 12/24/14
 * Time: 9:17 AM
 */

require_once(dirname(__FILE__) . '/class/lead-generator.class.php');
$config = parse_ini_file('config/api.ini', true);

try {
    
    $api = new RemoteApi\LeadGenerator(
        $config['resources']['login'], 
        $config['resources']['password']
    );

    $api->setApiUrl($config['resources']['url']);
    $api->connect();

    $fh = fopen(dirname(__FILE__) . '/../sample-resources/data-sample.csv', 'r');
    if(!$fh)
        die('Unable to open file');
    
    while (($data = fgetcsv($fh, 0, ',', '"')) !== false) {
        $lead = [];
        list(
            $lead['email'],
            $lead['birthdate'],
            $lead['gender'],
            $lead['firstName'],
            $lead['lastName'],
            $lead['originDetail']['campaignarea'],
            $lead['originDetail']['subid'],
            $lead['location']['country']
        ) = $data;

        $api->push($lead);
    }

} catch (UnexpectedValueException $e) {
    /*
     * Authentication missing or expired
     * */
    if (getenv('APP_ENV') === 'DEV') {
        echo PHP_EOL . print_r($e) . PHP_EOL;
    }
} catch (BadMethodCallException $e) {
    /*
    * The lead parameter format of the push method must be either an array or
    * */
    if (getenv('APP_ENV') === 'DEV') {
        echo PHP_EOL . print_r($e) . PHP_EOL;
    }
} catch (BadFunctionCallException $e) {
    /*
     * These exceptions are raised by the API
     * Please refer to the error description
     * */
    if (getenv('APP_ENV') === 'DEV') {
        echo PHP_EOL . print_r($e) . PHP_EOL;
    }
} catch (LogicException $e) {
    /*
     * The response body could not be parsed as a JSON array
     * check if headers of the response have been removed from the body
     * */
    if (getenv('APP_ENV') === 'DEV') {
        echo PHP_EOL . print_r($e) . PHP_EOL;
    }
} catch (Exception $e) {
    /*
     * Other exception
     * */
    if (getenv('APP_ENV') === 'DEV') {
        echo PHP_EOL . print_r($e) . PHP_EOL;
    }
}