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
		if (substr_count($response->getBody(), 'id="fb-root"') < 2) return;

		try {
			$doc = new DOMDocument;
			$doc->preserveWhiteSpace = false;
			libxml_use_internal_errors(true);
			$doc->loadhtml($response->getBody());
			libxml_use_internal_errors(false);

			$xpath = new DOMXPath($doc);

			$ns = $xpath->query('//div[@id="fb-root"]');
			foreach($ns as $i => $node) {
				if($ns->length <= ($i+1))
					continue;

				$node->parentNode->removeChild($node);
			}

			$response->setBody($doc->savehtml());
		} catch (\Exception $e) {

		}
	}
}