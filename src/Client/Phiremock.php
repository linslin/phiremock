<?php
namespace Mcustiel\Phiremock\Client;

use Mcustiel\Phiremock\Domain\Expectation;
use Psr\Http\Message\RequestInterface;
use Mcustiel\Phiremock\Client\Http\RemoteConnectionInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Uri;
use Zend\Diactoros\Stream;
use Mcustiel\SimpleRequest\RequestBuilder;

class Phiremock
{
    /**
     * @var \Mcustiel\Phiremock\Client\Http\RemoteConnectionInterface
     */
    private $connection;

    public function __construct(RemoteConnectionInterface $remoteConnection)
    {
        $this->connection = $remoteConnection;
    }

    public function createExpectation(Expectation $expectation)
    {
        $json = json_encode($expectation);
        /**
         * @var \Psr\Http\Message\RequestInterface $request
         */
        $request = new Request();
        $uri = new Uri();
        $uri->withPath(self::API_URL);
        $request->withUri($uri)->withMethod('post')->withBody(new Stream("data://text/plain,{$json}"));

        /**
         * @var \Psr\Http\Message\ResponseInterface $response
         */
        $response = $this->connection->send($request);

        if ($response->getStatusCode() === 201) {
            return;
        }

        if ($response->getStatusCode() >= 500) {
            $error = json_decode($response->getBody()->__toString())['details'];
            throw new \RuntimeException('An error occurred creating the expectation: ' . $error);
        }

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException('Request error while creating the expectation');
        }
    }

    public function clearExpectations()
    {
        /**
         * @var \Psr\Http\Message\RequestInterface $request
         */
        $request = new Request();
        $uri = new Uri();
        $uri->withPath(self::API_URL);
        $request->withUri($uri)->withMethod('delete');

        /**
         * @var \Psr\Http\Message\ResponseInterface $response
         */
        $response = $this->connection->send($request);

        if ($response->getStatusCode() === 201) {
            return;
        }

        if ($response->getStatusCode() >= 500) {
            $error = json_decode($response->getBody()->__toString())['details'];
            throw new \RuntimeException('An error occurred clearing the expectations: ' . $error);
        }

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException('Request error while clearing the expectations');
        }
    }

    public function listExpectations()
    {
        /**
         * @var \Psr\Http\Message\RequestInterface $request
         */
        $request = new Request();
        $uri = new Uri();
        $uri->withPath(self::API_URL);
        $request->withUri($uri)->withMethod('get');

        /**
         * @var \Psr\Http\Message\ResponseInterface $response
         */
        $response = $this->connection->send($request);

        if ($response->getStatusCode() === 201) {
            $json = json_decode($response->getBody()->__toString());
            $builder = new RequestBuilder();
            $return = [];
            foreach($json as $expectationArray) {
                $return[] = $builder->parseRequest($expectationArray, Expectation::class);
            }
            return $return;
        }

        if ($response->getStatusCode() >= 500) {
            $error = json_decode($response->getBody()->__toString())['details'];
            throw new \RuntimeException('An error occurred creating the expectation: ' . $error);
        }

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException('Request error while creating the expectation');
        }
    }

    public function resetScenarios()
    {

    }
}
