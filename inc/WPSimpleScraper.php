<?php
/**
 * Based on Simple Scraper (SimpleScraper.class.php) by Ramon Kayo
 *
 * Modified to do file fetching with core WordPress functions.
 *
 * @author Will Haynes <will@inn.org>
 * @author Ryan Nagle <ryan@inn.org>
 * @author Ramon Kayo <contato@ramonkayo.com>
 * @see license.txt
 * @since 0.1
 */
use \Exception;

class WPSimpleScraper {

	private
		$contentType,
		$data,
		$content,
		$httpCode,
		$url;

	/**
	 *
	 * @param string $url
	 * @throws Exception
	 */
	public function __construct($url) {
		$this->data = array(
			'ogp' => array(),
			'twitter' => array(),
			'meta' => array()
		);

		$urlPattern = '~^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$~iu';
		if (!is_string($url))
			throw new InvalidArgumentException("Argument 'url' is invalid (not a string).");
		if (!(preg_match($urlPattern, $url)))
			throw new InvalidArgumentException("Argument 'url' is invalid.");
		$this->url = $url;

		$this->fetchResource();
		libxml_use_internal_errors(true);
		$dom = new DOMDocument(null, 'UTF-8');

		$dom->loadHTML($this->content);
		$metaTags = $dom->getElementsByTagName('meta');

		for ($i=0; $i<$metaTags->length; $i++) {
			$attributes = $metaTags->item($i)->attributes;
			$attrArray = array();
			foreach ($attributes as $attr) $attrArray[$attr->nodeName] = $attr->nodeValue;

			if (
				array_key_exists('property', $attrArray) &&
				preg_match('~og:([a-zA-Z:_]+)~', $attrArray['property'], $matches)
			) {
				$this->data['ogp'][$matches[1]] = $attrArray['content'];
			} else if (
				array_key_exists('name', $attrArray) &&
				preg_match('~twitter:([a-zA-Z:_]+)~', $attrArray['name'], $matches)
			) {
				$this->data['twitter'][$matches[1]] = $attrArray['content'];
			} else if (
				array_key_exists('name', $attrArray) &&
				array_key_exists('content', $attrArray)
			) {
				$this->data['meta'][$attrArray['name']] = $attrArray['content'];
			}
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getAllData() {
		return $this->data;
	}

	/**
	 *
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 *
	 * @return string
	 */
	public function getContentType() {
		return $this->contentType;
	}

	/**
	 *
	 * @return string
	 */
	public function getHttpCode() {
		return $this->httpCode;
	}

	/**
	 *
	 * @return array
	 */
	public function getMeta() {
		return $this->data['meta'];
	}

	/**
	 *
	 * @return array
	 */
	public function getOgp() {
		return $this->data['ogp'];
	}

	/**
	 *
	 * @return array
	 */
	public function getTwitter() {
		return $this->data['twitter'];
	}

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
		$this->contentType = $response['headers']['content-type'];

		if (((int) $this->httpCode) >= 400) {
			throw new Exception('STATUS CODE: ' . $this->httpCode);
		}
	}

}
