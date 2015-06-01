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

class RemoveMultipleFbRoots implements RequestFilter {
	public function preRequest(SS_HTTPRequest $request, Session $session, DataModel $model) {
	}

	public function postRequest(SS_HTTPRequest $request, SS_HTTPResponse $response, DataModel $model) {
		$count = substr_count($response->getBody(), '<div id="fb-root"></div>');

		if ($count > 1)
			$response->setBody(preg_replace('/<div id="fb-root"><\/div>/', '', $count - 1));
	}
}