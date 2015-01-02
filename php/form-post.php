<?php
/**
 * User: charles
 * Date: 12/24/14
 * Time: 9:17 AM
 */

require_once(dirname(__FILE__) . '/class/lead-generator.class.php');
$config = parse_ini_file('config/api.ini', true);

$dob = new DateTime($_POST['dob']);
$lead = [
    'email' => $_POST['email'],
    'birthdate' => $dob->format(DateTime::ISO8601),
    'gender'    => $_POST['gender'],
    'firstName' => $_POST['first_name'],
    'lastName'  => $_POST['last_name'],
    'originDetail' => [
        'campaignarea' => 'NZ',
        'subid'        => '{SUBID}'
    ],
    'location' => [
        'country' => $_POST['country_iso']
    ]
];

$lead['location']['registrationIpV4'] = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] :
    (isset($_SERVER['HTTP_CLIENT_IP']) and !empty($_SERVER['HTTP_CLIENT_IP'])) ? $_SERVER['HTTP_CLIENT_IP'] : $_SERVER['REMOTE_ADDR'];

try {
    $api = new RemoteApi\LeadGenerator(
        $config['resources']['login'], 
        $config['resources']['password']
    );
    $api->setApiUrl($config['resources']['url']);
    $api->connect();
    $api->push($lead);

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