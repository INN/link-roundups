<?php
/**
 * Subclassing the simple scraper to do file fetching without
 * curl and within WordPress.
 */
use \Exception;

require_once dirname(__DIR__) . '/vendor/simple-scraper/SimpleScraper.class.php';

class WPSimpleScraper extends SimpleScraper {

	/**
	 * Override fetchResource to do it with wp_fetch_urla
	 */
	private function fetchResource() {

		$cookies = array();
		foreach ( $_COOKIE as $name => $value ) {
			$cookies[] = new WP_Http_Cookie( array( 'name' => $name, 'value' => $value ) );
		}

		$response = wp_remote_get(
			$this->url, array(
				'cookies' => $cookies,
				'headers' => array( 'Accept-Encoding' => 'gzip' ),
				'user-agent'=> 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36'
			));

		$this->content = wp_remote_retrieve_body($response);
		$this->httpCode = wp_remote_retrieve_response_code($response);
		$this->contentType = $response['headers']['content_type'];

		if (((int) $this->httpCode) >= 400) {
			throw new Exception('STATUS CODE: ' . $this->httpCode);
		}
	}

}
