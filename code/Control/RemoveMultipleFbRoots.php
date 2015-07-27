<?php namespace Milkyway\SS\SocialFeed\Control;
/**
 * Milkyway Multimedia
 * RemoveMultipleFbRoots.php
 *
 * @package yellowbrickroad.com.au
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use RequestFilter;
use SS_HTTPRequest;
use Session;
use DataModel;
use SS_HTTPResponse;
use DOMDocument;
use DOMXPath;

class RemoveMultipleFbRoots implements RequestFilter {
	public function preRequest(SS_HTTPRequest $request, Session $session, DataModel $model) {
	}

	public function postRequest(SS_HTTPRequest $request, SS_HTTPResponse $response, DataModel $model) {
		if(singleton('env')->get('include_facebook_root_div'))
			$response->setBody(str_replace('<body>', '<body><div id="fb-root"></div>', $response->getBody()));
	}
}