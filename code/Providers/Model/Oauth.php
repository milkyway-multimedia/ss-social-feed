<?php namespace Milkyway\SS\SocialFeed\Providers\Model;

use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Milkyway\SS\Config;

/**
 * Milkyway Multimedia
 * HTTPProvider.php
 *
 * @package reggardocolaianni.com
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */
abstract class Oauth extends HTTP {
	protected $oauth2provider;

    protected $credentials = [
        'consumer_key'    => '',
        'consumer_secret' => '',
        'token'           => '',
        'token_secret'    => '',
    ];

	protected $accessToken;

    public function __construct($cache = 6, array $credentials = []) {
        parent::__construct($cache);
        $this->credentials = $credentials;
    }

	/**
	 * This is a very basic Oauth2 caller, limited for use to this module
	 * for things such as displaying ratings etc., which requires scopes
	 *
	 * By the way, I strongly recommend you change the directory this is stored in
	 * if your TEMP_DIR is constantly cleaned
	 */
	public function extendedPermissions($scopes = [], $noLiveRequest = true, $redirectTo = '') {
		if(!class_exists('League\OAuth2\Client\Token\AccessToken'))
			throw new \LogicException('You cannot use extended permissions without the league/oauth2-client module');

		if(count($scopes))
			$this->credentials['scopes'] = $scopes;

		if(!$redirectTo)
			$redirectTo = isset($_SERVER['REQUEST_URI']) ? \Director::absoluteURL($_SERVER['REQUEST_URI']) : \Director::absoluteURL(\Controller::curr()->Link());

		$this->credentials['redirectUri'] = $this->removeFromQueryString(['code', 'state'], $redirectTo);
		$this->credentials['clientId'] = $this->credentials['consumer_key'];
		$this->credentials['clientSecret'] = $this->credentials['consumer_secret'];

		$this->accessToken = $this->accessToken($noLiveRequest);

		return $this;
	}

    protected function http()
    {
        parent::http();

        $this
            ->client
            ->getEmitter()
            ->attach(new Oauth1($this->credentials));

        return $this->client;
    }

    protected function getHttpSettings() {
	    $settings = [
		    'defaults' => [
			    'auth' => $this->accessToken ? 'oauth2' : 'oauth',
		    ],
	    ];

        return array_merge(parent::getHttpSettings(), $settings);
    }

	protected function getBodyFromCache($url, $settings = []) {
		if($this->accessToken && !(isset($settings['query']) && isset($settings['query']['access_token']))) {
			$settings['query']['access_token'] = $this->accessToken;
		}

		return parent::getBodyFromCache($url, $settings);
	}

	protected function accessToken($noLiveRequest = true) {
		$tokenLocation = $this->tokenLocation();
		$accessToken = null;

		if(file_exists($tokenLocation)) {
			$token = json_decode(file_get_contents($tokenLocation));

			if(isset($token->accessToken))
				$accessToken = $token->accessToken;
		}

		if(!$accessToken && !$noLiveRequest) {
			$token = $this->retrieveAccessToken();

			if(isset($token->accessToken))
				$accessToken = $token->accessToken;
		}

		return $accessToken;
	}

	protected function retrieveAccessToken() {
		$provider = \Object::create($this->oauth2provider, $this->credentials);

		if (!isset($_GET['code'])) {
			$url = $provider->getAuthorizationUrl();
			\Session::set(get_called_class() . '--oauth2state', $provider->state);
			return \Controller::curr()->redirect($url);
		} elseif (empty($_GET['state']) || ($_GET['state'] !== \Session::get(get_called_class() . '--oauth2state'))) {
			\Session::clear(get_called_class() . '--oauth2state');
			throw new \Exception('Invalid state');
		} else {
			$token = $provider->getAccessToken('authorization_code', array_merge($this->credentials, [
				'code' => $_GET['code'],
			]));

			file_put_contents($this->tokenLocation(), json_encode($token));

			return $token;
		}
	}

	protected function removeFromQueryString($key, $url) {
		foreach((array)$key as $k)
			$url = substr(preg_replace('/(.*)(\?|&)' . preg_quote($k) . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&'), 0, -1);

		return $url;
	}

	private function tokenLocation() {
		$folder = Config::get('SocialFeed.token_directory') ?: TEMP_FOLDER;
		return $folder . DIRECTORY_SEPARATOR . '.' . str_replace('\\', '__' , get_class($this)) . '_token';
	}
} 