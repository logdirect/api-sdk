<?php
/**
 * User: charles
 * Date: 1/2/15
 * Time: 8:44 AM
 */

namespace RemoteApi;
require_once('api-connection.class.php');

class LeadGenerator extends APIConnection
{
    protected $lead;
    
    public function push($lead)
    {
        $this->lead = $lead;

        if (! $this->token->{'id'}) {
            throw new \ErrorException('You must connect BEFORE to push a lead');
        }

        switch (true) {
            case is_string($this->lead):
                $check = json_decode($this->lead);
                if (!$check) {
                    throw new \BadMethodCallException(
                        sprintf('Lead JSON string "%s" is not valid, error: "%s"',
                            $this->curlResponse,
                            json_last_error_msg()
                        )
                    );
                }
                $this->postBody = $this->lead;
                break;
            case is_array($this->lead):
                $this->postBody = json_encode($this->lead);
                break;
            default:
                throw new \BadMethodCallException('Lead should be either a JSON string or an array');
        }

        $this::curlPost($this->apiUrl . 'Customers', $this->postBody);
    }
}