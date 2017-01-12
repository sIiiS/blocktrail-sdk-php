<?php

namespace Blocktrail\SDK\Connection;


class IlluminateRestClient extends RestClient implements RestClientInterface
{
    public function __construct($apiEndpoint, $apiVersion, $apiKey, $apiSecret) {
        parent::__construct($apiEndpoint, $apiVersion, $apiKey, $apiSecret);
    }

    /**
     * generic request executor
     *
     * @param   string          $method         GET, POST, PUT, DELETE
     * @param   string          $endpointUrl
     * @param   array           $queryString
     * @param   array|string    $body
     * @param   string          $auth           http-signatures to enable http-signature signing
     * @param   string          $contentMD5Mode body or url
     * @param   float           $timeout        timeout in seconds
     * @return Response
     */
    public function request($method, $endpointUrl, $queryString = null, $body = null, $auth = null, $contentMD5Mode = null, $timeout = null) {
        $request = $this->buildRequest($method, $endpointUrl, $queryString, $body, $contentMD5Mode);
        $response = $this->guzzle->send($request, ['auth' => $auth, 'timeout' => $timeout]);

        return $this->responseHandler($response);
    }

}