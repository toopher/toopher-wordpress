<?php

/*
Copyright (c) 2012 Toopher, Inc

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

class ToopherRequestException extends Exception
{
    
}

class ToopherAPI
{
    protected $baseUrl;
    protected $oauthConsumer;
    protected $httpAdapter;

    function __construct($key, $secret, $baseUrl = '', $httpAdapter = NULL)
    {
        if(empty($key))
        {
            throw new InvalidArgumentException('Toopher consumer key cannot be empty');
        }
        if(empty($secret))
        {
            throw new InvalidArgumentException('Toopher consumer secret cannot be empty');
        }

        require_once(__DIR__.'/vendor/autoload.php');
        $this->oauthConsumer = new HTTP_OAuth_Consumer($key, $secret);
        $this->baseUrl = (!empty($baseUrl)) ? $baseUrl : 'https://api.toopher.com/v1/';
        $this->httpAdapter = (!is_null($httpAdapter)) ? $httpAdapter : new HTTP_Request2_Adapter_Curl();
    }

    public function pair($pairingPhrase, $userName, $extras = array())
    {
        $params = array(
            'pairing_phrase' => $pairingPhrase,
            'user_name' => $userName
        );
        $params = array_merge($params, $extras);
        return $this->makePairResponse($this->post('pairings/create', $params));
    }

    public function getPairingStatus($pairingId)
    {
        return $this->makePairResponse($this->get('pairings/' . $pairingId));
    }

    public function authenticate($pairingId, $terminalName, $actionName = '', $extras = array())
    {
        $params = array(
            'pairing_id' => $pairingId,
            'terminal_name' => $terminalName
        );
        if(!empty($actionName))
        {
            $params['action_name'] = $actionName;
        }
        $params = array_merge($params, $extras);
        return $this->makeAuthResponse($this->post('authentication_requests/initiate', $params));
    }

    public function getAuthenticationStatus($authenticationRequestId)
    {
        return $this->makeAuthResponse($this->get('authentication_requests/' . $authenticationRequestId));
    }

    private function makePairResponse($result)
    {
        return array(
            'id' => $result['id'],
            'enabled' => $result['enabled'],
            'userId' => $result['user']['id'],
            'userName' => $result['user']['name'],
            'raw' => $result
        );
    }

    private function makeAuthResponse($result)
    {
        return array(
            'id' => $result['id'],
            'pending' => $result['pending'],
            'granted' => $result['granted'],
            'automated' => $result['automated'],
            'reason' => $result['reason'],
            'terminalId' => $result['terminal']['id'],
            'terminalName' => $result['terminal']['name'],
            'raw' => $result
        );
    }

    private function post($endpoint, $parameters)
    {
        return $this->request('POST', $endpoint, $parameters);
    }

    private function get($endpoint)
    {
        return $this->request('GET', $endpoint);
    }

    private function request($method, $endpoint, $parameters = array())
    {
        $req = new HTTP_Request2();
        $req->setAdapter($this->httpAdapter);
        $req->setMethod($method);
        $req->setUrl($this->baseUrl . $endpoint);
        if(!is_null($parameters))
        {
            foreach($parameters as $key => $value)
            {
                $req->addPostParameter($key, $value);
            }
        }
        $oauthRequest = new HTTP_OAuth_Consumer_Request;
        $oauthRequest->accept($req);
        $this->oauthConsumer->accept($oauthRequest);
        $result = $this->oauthConsumer->sendRequest($this->baseUrl . $endpoint, $parameters, $method);
        $decoded = json_decode($result->getBody(), true);

        if(array_key_exists("error_message", $decoded))
        {
            throw new ToopherRequestException($decoded['error_message'], $decoded['error_code']);
        }
        return $decoded;   
    }
}

?>
