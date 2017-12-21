<?php
/** MIT License */
namespace janrain\features;

use janrain\Feature;
use janrain\Profile;
use janrain\ProfileSchema;
use Guzzle\Http\Client;

use janrain\Adapter;

class CaptureApi extends Feature
{
    protected $reqHeaders;
    protected $guzzle;

    public function __construct(Adapter $cms)
    {
        parent::__construct($cms);
        $this->reqHeaders = array();
        $this->reqHeaders['Accept-Encoding'] = 'identity';
        $this->reqHeaders['Content-type'] = 'application/x-www-form-urlencoded';
        $this->guzzle = new Client();

        // set base agent header.
        $this->guzzle->setDefaultOption('headers/User-Agent', \janrain\Sdk::getUserAgent());

        // set referer header.
        $this->guzzle->setDefaultOption('headers/Referer', \janrain\Sdk::getReferrer());
    }

    public function getTransport()
    {
        return $this->guzzle;
    }

    /**
     * Make a call against a capture endpoint
     */
    public function __invoke($endpoint, $params, $token = null)
    {
        if ($token) {
            $this->reqHeaders["Authorization"] = "OAuth {$token}";
        } else {
            $this->signRequest($endpoint, $params);
        }
        // late bind the baseUrl (useful when using feature during feature validation
        if (!$this->guzzle->getBaseUrl()) {
            $this->guzzle->setBaseUrl($this->config->getItem('capture.captureServer'));
        }
        $resp = $this->guzzle->post($endpoint, $this->reqHeaders, $params)->send()->json();
        if (empty($resp['stat']) || $resp['stat'] == 'error') {
            throw new \Exception($resp['error_description']);
        }
        return isset($resp['result']) ? $resp['result'] : $resp;
    }

    private function signRequest($url, &$params)
    {
        #no token found, use message signing so we never transfer the client_secret
        ksort($params);
        $timeStr = gmdate('Y-m-d H:i:s');
        $this->reqHeaders["Date"] = $timeStr;
        $data = "/{$url}\n{$timeStr}\n";
        foreach ($params as $k => $v) {
            $data .= "{$k}={$v}\n";
        }
        $rawDigest = hash_hmac('sha1', $data, $this->config->getItem('capture.clientSecret'), true);
        $b64 = base64_encode($rawDigest);
        $this->reqHeaders["Authorization"] = sprintf(
            "Signature %s:%s",
            $this->config->getItem('capture.clientId'),
            $b64
        );
    }

    public function oauthRefreshToken($refreshToken)
    {
        $params = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            );
        $resp = $this('oauth/token', $params);
        return (object) $resp;
    }

    public function fetchTokensFromCode($code, $redirectUri)
    {
        $params = array(
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
            );
        $resp = $this('oauth/token', $params);
        return $resp;
    }

    /**
    * Fetch the profile for the given access token
    *
    * Convenience method for grabbing the entire capture profile, most likely used for mapping data fields. Note: this
    * is a blunt instrument. For performance, you may wish to use entity() and only pull the necessary fields.
    *
    * @param string $token  A valid capture access token.
    *
    * @return Profile
    */
    public function fetchProfileByToken($token)
    {
        $data = $this->entity(null, $token);
        return new Profile($data);
    }

    /**
     * Convenience method for only grabbing a subset of the profile which is used for CMS logins. This is MUCH smaller
     * than the entire profile and can therefore be used for faster/predictable login/registration speed.  Full profile
     * fetching should be used when attempting to "map" data
     */
    public function fetchProfileIdentityByToken($token)
    {
        $opts = array('attributes' => '["uuid","/profiles/identifier","email","emailVerified","displayName"]');
        $data = $this->entity($opts, $token);
        return new Profile($data);
    }

    /**
     * Direct call to capture entity endpoint
     *
     * @param Array $options All the arguments and params you'd specify to entity. Defaults to ['type_name' => 'user']
     *
     * @param mixed $accessToken Optionally specify the accessToken to bypass PSK authorization.
     *
     * @throws InvalidParameterException Specifying options the endpoint doesn't support throws an exception.
     */
    public function entity(array $options = null, $accessToken = null)
    {
        if (empty($options)) {
            $options = array('type_name' => 'user');
        }
        static $allowedOptions = array('client_secret', 'client_id', 'access_token', 'uuid', 'id', 'key_attribute',
            'key_value', 'password_attribute', 'password_value', 'type_name', 'attribute_name', 'attributes', 'created',
            'last_updated');
        $diff = array_diff(array_keys($options), $allowedOptions);
        if (count($diff)) {
            throw new \InvalidParameterException('Unsupported options specified!');
        }
        if ($accessToken) {
            return $this('entity', $options, $accessToken);
        }
        return $this('entity', $options);
    }

    /**
     * Get the json for a capture registration schema
     *
     * @param string $type The entity type name. Defaults to "user"
     *
     * @return stdClass The capture schema converted to native php for easy
     *   navigation/processing
     */
    public function entityType($type = 'user')
    {
        $resp = $this('entityType', array('type_name' => $type));
        return $resp['schema'];
    }

    public function settingsItems()
    {
        $resp = $this('settings/items', array());
        $this->config->setItem('apiKey', $resp['rpx_key']);
        \janrain\Sdk::instance()->EngageApi->fetchSettings($resp['rpx_key']);
        $this->config->setItem('capture.appId', @$resp['plex_app_id']);
        if (isset($resp['backplane_server'])) {
            $this->config->setItem('backplane.serverBaseUrl', 'https://' . $resp['backplane_server'] . '/v1.2');
            $this->config->setItem('backplane.busName', $resp['backplane_bus']);
        }
        if (isset($resp['plex_sso_server'])) {
            $this->config->setItem('sso.server', $resp['plex_sso_server']);
        }
        return $resp;
    }

    protected static $OPTS_U = array(
        'capture.captureServer' => array('IsUrl'),
        'capture.clientId' => array('NotEmpty'),
        'capture.clientSecret' => array('NotEmpty'));
    protected static $OPTS_J = array(
        'apiKey' => array('NotEmpty'));
}
