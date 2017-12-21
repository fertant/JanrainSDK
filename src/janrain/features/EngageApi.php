<?php
/** MIT License */
namespace janrain\features;

use janrain\Feature;
use janrain\platform\HttpPostApi;
use janrain\Profile;
use Guzzle\Http\Client;
use janrain\Adapter;

class EngageApi extends Feature implements HttpPostApi
{
    protected $guzzle;

    public function __construct(Adapter $cms)
    {
        parent::__construct($cms);
        $this->guzzle = new Client();

        // set base agent header.
        $this->guzzle->setDefaultOption('headers/User-Agent', \janrain\Sdk::getUserAgent());

        // set referer header.
        $this->guzzle->setDefaultOption('headers/Referer', \janrain\Sdk::getReferrer());
    }

    /**
     * {@inheritdoc}
     */
    public function getTransport()
    {
        return $this->guzzle;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($endpoint, array $params = array(), array $headers = array())
    {
        // late bind the baseUrl (useful when using feature during feature validation
        $this->guzzle->setBaseUrl($this->config->getItem('appUrl') . '/api/v2');
        $resp = $this->guzzle->post($endpoint, array(), $params)->send()->json();
        if (empty($resp['stat']) || $resp['stat'] != 'ok') {
            throw new \Exception(sprintf('Error #%d: %s', $resp['err']['code'], $resp['err']['msg']));
        }
        $this->reqHeaders = array();
        return $resp;
    }

    /**
     * Use the apiKey to fetch all necessary configuration from Engage
     *
     * This actually uses the guzzle object in a side channel, therefore doesn't
     * invoke the __invoke method used for other calls.
     *
     * @return Array
     *      Returns json response deserialized into array structure.
     *
     * @throws Guzzle\Http\Exception\ClientErrorResponseException
     *      If engage rejects the call (invalid api secret) or a network problem
     *      occurs.
     */
    public function fetchSettings()
    {
        $key = $this->config->getItem('apiKey');
        $query = http_build_query(array('apiKey' => $key));
        $settings = $this->guzzle->get('https://rpxnow.com/plugin/lookup_rp?'. $query)->send()->json();
        $appUrl = sprintf('https://%s', $settings['realm']);
        $this->config->setItem('externalId', $settings['externalId']);
        $this->config->setItem('appUrl', $appUrl);
        if (!empty($settings['ssoServer'])) {
            $this->config->setItem('sso.server', 'https://' . $settings['ssoServer']);
        }
        $shareProvs = $settings['shareProviders'];
        if (!empty($shareProvs)) {
            if (is_string($shareProvs)) {
                $this->config->setItem('social.providers', explode(',', $shareProvs));
            } elseif (is_array($shareProvs)) {
                $this->config->setItem('social.providers', $shareProvs);
            }
        } else {
            $this->config->setItem('social.providers', array());
        }
        $bpServer = $this->getBackplaneProperties();
        if ($bpServer) {
            $bpBaseUrl = sprintf('https://%s/%s', $bpServer['server'], $bpServer['version']);
            $this->config->setItem('backplane.serverBaseUrl', $bpBaseUrl);
            $this->config->setItem('backplane.busName', $bpServer['bus']);
        }
        return $settings;
    }

    /**
     * Convenience method for getting backplane properties from api.
     *
     * @return Array
     *      array converted json response.
     *
     * @throws Guzzle\Http\Exception\ClientErrorResponseException
     *      bubbles up guzzle error @todo fix this
     */
    public function getBackplaneProperties()
    {
        $params = array('apiKey' => $this->config->getItem('apiKey'));
        $resp = $this('get_backplane_properties', $params);
        $servers = $resp['backplane_servers'];
        switch (count($servers)) {
            case 1:
                return $servers[0];
                break;
            case 0:
                return null;
                break;
            default:
                throw new \Exception('Backplane misconfigured for this app!');
        }
    }

    public function authInfo($accessToken)
    {
        $params = array(
            'apiKey' => $this->config->getItem('apiKey'),
            'token' => $accessToken);
        return $this('auth_info', $params);
    }

    public function fetchProfileByToken($accessToken)
    {
        $resp = $this->authInfo($accessToken);
        return new Profile($resp);
    }

    protected static $OPTS_U = array('apiKey' => array('NotEmpty'));
    protected static $OPTS_J = array('appUrl' => array('IsUrl'));
}
