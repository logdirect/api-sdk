<?php
/**
 * User: charles
 * Date: 12/24/14
 * Time: 9:18 AM
 */

namespace RemoteApi;

class APIConnection
{

    protected $apiUrl = '';
    protected $authParams = [
        'username' => '',
        'password' => ''
    ];
    protected $token = '';
    protected $lead;
    protected $postBody = '';
    protected $curlOptions = [];
    protected $partnerName = '';
    protected $body;
    protected $curlHandler;
    protected $curlStatus;
    protected $curlResponse;

    protected function APIErrorHandler ($error)
    {
        $status = json_decode($error);

        switch ($status->{'error'}->{'code'}) {
            case 'L072':
                $shortMessage = 'Partner (yours) account disabled';
                break;
            case 'L073':
                $shortMessage = 'Partner (yours) quota reached';
                break;
            case 'L071':
                $shortMessage = 'Partner (you) not found';
                break;
            case 'L052':
                $shortMessage = 'Customer has not been saved';
                break;
            case 'L081':
            case 'L082':
                $shortMessage = 'Data provided (typically date of birth) seems to be incorrect';
                break;
            case 'L304':
            case 'L308':
                $shortMessage = 'Authentication missing or expired';
                throw new \UnexpectedValueException($shortMessage);
                break;
            case 'L301':
            case 'L305':
            case 'L337':
                $shortMessage = 'Restricted area. Please contact your business partner';
                break;
            case 'L306':
                $shortMessage = 'ESP: Domain blacklisted - We don not accept email of this domain';
                break;
            case 'L307':
                $shortMessage = 'ESP: Account blacklisted - That kind of address is not allowed';
                break;
            case 'L331':
            case 'L332':
            case 'L333':
            case 'L334':
            case 'L335':
            case 'L338':
                $shortMessage = 'Generic error. For more detail please contact your business partner';
                break;
            case 'L336':
                $shortMessage = 'Bad range date';
                break;
            case 'L339':
                $shortMessage = 'Email is required';
                break;
            case 'L351':
                $shortMessage = 'Email no valid';
                break;
            case 'L345':
                $shortMessage = 'Item (email) already exist';
                break;
            case 'L340':
                $shortMessage = 'Birth date is required';
                break;
            case 'L346':
                $shortMessage = 'Date is not in a right format. Please use ISO 8601 date format';
                break;
            case 'L341':
                $shortMessage = 'Missing params';
                break;
            case 'L342':
                $shortMessage = 'Invalid value';
                break;
            case 'L343':
                $shortMessage = 'GeoIP Invalid value. The country provided might be black listed';
                break;
            default:
                $shortMessage = 'An unknown error was returned';
                break;
        }

        throw new \BadFunctionCallException(
            sprintf('API Error [%s]: %s
            Detail: %s', $status->{'error'}->{'code'}, $shortMessage, $status->{'error'}->{'message'})
        );
    }
    
    protected function curlPost($url, $body)
    {
        $this->curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body)
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => 'utf-8',
            CURLOPT_USERAGENT => 'UA-RemoteAPI/0.0.1',
            CURLOPT_AUTOREFERER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_VERBOSE => 0
        ];
        
        if (getenv('APP_ENV') === 'PROD') {
            /*
             * Production mode:
             * Certificates must be checked during the request
             * */
            $this->curlOptions[CURLOPT_SSL_VERIFYHOST] = 2;
            $this->curlOptions[CURLOPT_SSL_VERIFYPEER] = 1;
        } else {
            /*
             * Other modes:
             * On the testing API do not check SSL certificate to avoid an ERROR
             * */
            $this->curlOptions[CURLOPT_SSL_VERIFYHOST] = 0;
            $this->curlOptions[CURLOPT_SSL_VERIFYPEER] = 0;
        }

        if ($this->token and $this->token->{'id'}) {
            array_push($this->curlOptions[CURLOPT_HTTPHEADER], 'Authorization: ' . $this->token->{'id'});
        }

        curl_setopt_array($this->curlHandler, $this->curlOptions);
        $this->curlResponse = curl_exec($this->curlHandler);

        if (curl_errno($this->curlHandler)) {
            throw new \Exception('cURL ERROR [' . print_r(curl_errno($this->curlHandler), true) . ']: ' .
                print_r(curl_error($this->curlHandler), true));
        }

        $this->curlStatus = curl_getinfo($this->curlHandler);
        if (!$this->token) {
            $this->token = json_decode($this->curlResponse);
            if (!$this->token) {
                throw new \LogicException(
                    sprintf('Failed to parse connection JSON string "%s", error: "%s"',
                        $this->curlResponse,
                        json_last_error_msg()
                    )
                );
            }
        }
        
        if ($this->curlStatus['http_code'] <> 200) {
            $this::APIErrorHandler($this->curlResponse);
        }

    }
    
    public function __construct($username, $password)
    {
        if (!isset($username) or empty($username)) {
            throw new \Exception('Login is require');
        }
        if (!isset($password) or empty($password)) {
            throw new \Exception('Password is require');
        }

        $this->authParams['username'] = $username;
        $this->authParams['password'] = $password;
        $this->partnerName = strtoupper($username);
    }

    public function connect($url = '')
    {
        if (isset($url) and !empty ($url)) {
            $this->apiUrl = $url;
        }

        if (!$this::__isset('apiUrl')) {
            throw new \Exception('URL must be set before call');
        }

        $this->curlHandler = curl_init();
        $this::curlPost($this->apiUrl . 'Partners/login', json_encode($this->authParams));
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }
    
    function __toString()
    {
        return print_r($this->curlStatus, true);
    }

    function __isset($name)
    {
        return (!isset($this->$name) or empty($this->$name)) ? false : true;
    }

    function __destruct()
    {
        curl_close($this->curlHandler);
    }
}