<?php
/** MIT License */
namespace janrain\platform;

use Guzzle\Http\ClientInterface;

/**
 * Inteface for implmenting calls against Janrain's semi-RESTful services.
 *
 * Implementers of this interface should provide a mechanism for
 */
interface HttpPostApi
{
    /**
     * Post the HTTP request and return the response body.
     *
     * @param string $apiEndpoint
     *      The endpoint to call (such as /entity or /oauth/token)
     *
     * @param Array $requestParams
     *      *optional* array of request parameters in post body
     *
     * @param Array $headers
     *      *optional* Custom headers to send with the request. [headerName=>headerValue]
     *
     * @return StdClass|null
     *      The response should be a json_decoded php object representing the
     *      response from the api call.
     *
     * @throws Exception
     *      Will throw exceptions for network and restful errors
     */
    public function __invoke($apiEndpoint, array $requestParams = array(), array $headers = array());

    /**
     * Configure the transport mechanism.
     *
     * Should it be necessary, you may implement your own http client wrapper.
     * Maybe curl, or stream contexts, etc.  However, you must implement Guzzle's
     * HttpClient interface.
     *
     * @param ClientInterface $c
     *      *optional* The guzzle httpclient (or alternate implementation) to use for all
     *      subsequent API calls. If no client is provided, the default guzzle
     *      client will be created.
     *
     * @return null
     */
    // public function setTransport(ClientInterface $c = null);

    /**
     * Get the underlying Guzzle transport
     *
     * Mainly to instrument for mocking responses.
     *
     * @return ClientInterface
     *      The guzzle client used for api calls.
     */
    public function getTransport();
}
