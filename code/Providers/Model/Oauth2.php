<?php namespace Milkyway\SS\SocialFeed\Providers\Model;

/**
 * Milkyway Multimedia
 * Oauth2.php
 *
 * @package milkyway-multimedia/ss-social-feed
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Milkyway\SS\SocialFeed\Contracts\RequiresOauth2;

use LogicException;
use Controller;
use Session;
use SS_HTTPResponse_Exception;
use Exception;
use Object;
use Director;
use Debug;

abstract class Oauth2 extends HTTP implements RequiresOauth2, \Flushable
{
    protected $provider;
    protected $providerClass;

    protected $accessToken;

    protected $configuration = [];

    /**
     * Clear session tokens on flush
     */
    public static function flush() {
        Session::clear('SocialFeed.access_tokens');
    }

    /**
     * This is a very basic Oauth2 caller, limited for use to this module
     * for things such as displaying ratings etc., which requires scopes
     *
     * By the way, I strongly recommend you change the directory this is stored in
     * if your TEMP_DIR is constantly cleaned
     */
    public function extendedPermissions($params = null)
    {
        if (!class_exists('League\OAuth2\Client\Token\AccessToken')) {
            throw new LogicException('You cannot use extended permissions without the league/oauth2-client module');
        }

        if (!empty($params['scopes']) && $params['scopes'] !== '_all') {
            $this->configuration['scope'] = $params['scopes'];
        }

        if (empty($params['redirect_to'])) {
            $params['redirect_to'] = isset($_SERVER['REQUEST_URI']) ? singleton('director')->absoluteURL($_SERVER['REQUEST_URI']) : singleton('director')->absoluteURL(Controller::curr()->Link());

            // Ouch, hacky, but making it easier for you to follow the redirect url rules for some oauth providers... Looking at you instagram!
//            $params['redirect_to'] = Controller::join_links(singleton('director')->absoluteBaseURL(), '?url=' . singleton('director')->makeRelative($params['redirect_to']));
        }

        $this->configuration['redirectUri'] = Controller::join_links($this->removeFromQueryString(['code', 'state', 'scope', 'redirectUri'], $params['redirect_to']));

        $this->accessToken = $this->accessToken(!empty($params['no_live_request']));

        return $this;
    }

    protected function request($url, $settings = []) {
        if(!$this->accessToken) {
            return parent::request($url, $settings);
        }

        return $this->client->sendAsync($this->provider()->getAuthenticatedRequest('GET', $url, $this->accessToken, $settings), $settings);
    }

    protected function provider() {
        if(!$this->provider) {
            $this->provider = Object::create(
                $this->providerClass,
                $this->configuration,
                array_merge(
                    [
                        'httpClient' => $this->client,
                    ],
                    singleton('env')->get(get_called_class() . '|' . __CLASS__ . '|SocialFeed_Oauth2.collaborators', [
                        'requestFactory' => Object::create('Milkyway\SS\SocialFeed\Providers\Common\RequestFactory'),
                    ])
                )
            );
        }

        return $this->provider;
    }

    protected function accessToken($noLiveRequest = true)
    {
        $tokenLocation = $this->tokenLocation();
        $accessToken = null;

        if($accessToken = Session::get('SocialFeed.access_tokens.' . get_called_class() . '.' . $this->tokenKey())) {
            return $accessToken;
        }

        if(!empty($this->configuration['access_token'])) {
            $accessToken = $this->configuration['access_token'];
        }

        if (file_exists($tokenLocation)) {
            $token = unserialize(file_get_contents($tokenLocation));

            if (isset($token->accessToken)) {
                $accessToken = $token->accessToken;
            }
            else if($token instanceof \League\OAuth2\Client\Token\AccessToken) {
                $accessToken = $token->getToken();
            }
        }

        if (!$accessToken && !$noLiveRequest) {
            $token = $this->retrieveAccessToken();

            if (isset($token->accessToken)) {
                $accessToken = $token->accessToken;
            }
            else if($token instanceof \League\OAuth2\Client\Token\AccessToken) {
                $accessToken = $token->getToken();
            }
        }

        if($accessToken) {
            Session::set('SocialFeed.access_tokens.' . get_called_class() . '.' . $this->tokenKey(), $accessToken);
        }

        return $accessToken;
    }

    protected function retrieveAccessToken()
    {
        if(Controller::curr()->redirectedTo() || Controller::curr()->Response->getHeader('X-SocialFeed-RedirectForOauth')) {
            return null;
        }

        $key = $this->tokenKey();

        if (!isset($_GET['code'])) {
            if(!empty($this->configuration['consumer_key'])) {
                if(empty($this->configuration['clientId'])) {
                    $this->configuration['clientId'] = $this->configuration['consumer_key'];
                }

                unset($this->configuration['consumer_key']);
            }

            if(!empty($this->configuration['consumer_secret'])) {
                if(empty($this->configuration['clientSecret'])) {
                    $this->configuration['clientSecret'] = $this->configuration['consumer_secret'];
                }

                unset($this->configuration['consumer_secret']);
            }

            $url = Session::get(get_called_class() . '.oauth2state.' . $key . '.url');

            if(!$url) {
                $url = $this->provider()->getAuthorizationUrl($this->configuration);

                Session::set(get_called_class() . '.oauth2state.' . $key, [
                    'url' => $url,
                    'state' => $this->provider()->getState(),
                ]);
            }

            if(isset($_GET['flush'])) {
                Session::clear(get_called_class() . '.oauth2state.' . $key . '.url');
            }

            if(singleton('env')->get('SocialFeed.disable_oauth_redirects')) {
                if(Director::isDev()) {
                    Debug::show($url);
                }
            }
            else {
                if (Controller::curr()->Request->isAjax()) {
                    Controller::curr()->Response->addHeader('X-SocialFeed-RedirectForOauth', $url);
                } else {
                    return Controller::curr()->redirect($url);
                }
            }

        } elseif (empty($_GET['state']) || ($_GET['state'] != Session::get(get_called_class() . '.oauth2state.' . $key . '.state'))) {
            throw new SS_HTTPResponse_Exception('Could not get access token', 403);

        } else {
            try {
                $token = $this->provider()->getAccessToken('authorization_code', [
                    'code' => $_GET['code'],
                ]);

                $token = $this->provider()->getLongLivedAccessToken($token);

                file_put_contents($this->tokenLocation(), serialize($token));
                Session::set('SocialFeed.access_tokens.' . get_called_class() . '.' . $this->tokenKey(), $token->getToken());
            } catch (Exception $e) {
//                throw $e;
                throw new SS_HTTPResponse_Exception('Could not get access token: ' . $e->getMessage(), 403);
            }

            return $token;
        }
    }

    protected function removeFromQueryString($key, $url)
    {
        foreach ((array)$key as $k) {
            $url = substr(preg_replace('/(.*)(\?|&)' . preg_quote($k) . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&'), 0,
                -1);
        }

        return $url;
    }

    protected function tokenKey() {
        return !empty($this->configuration['scope']) ? singleton('mwm')->clean_cache_key('SocialFeed', $this->configuration['scope']) : 0;
    }

    private function tokenLocation()
    {
        $folder = singleton('env')->get('SocialFeed.token_directory') ?: TEMP_FOLDER;
        return $folder . DIRECTORY_SEPARATOR . '.' . str_replace('\\', '__', get_called_class()) . $this->tokenKey() . '_token';
    }
} 