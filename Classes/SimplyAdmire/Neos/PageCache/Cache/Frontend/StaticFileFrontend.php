<?php
namespace SimplyAdmire\Neos\PageCache\Cache\Frontend;

use TYPO3\Flow\Cache\Exception\InvalidDataException;
use TYPO3\Flow\Cache\Frontend\StringFrontend;

class StaticFileFrontend extends StringFrontend {

	/**
	 * Saves the value of a PHP variable in the cache.
	 *
	 * @param string $entryIdentifier An identifier used for this cache entry
	 * @param array $data The variable to cache
	 * @param array $tags Tags to associate with this cache entry
	 * @param integer $lifetime Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited lifetime.
	 * @return void
	 * @throws InvalidDataException
	 * @throws \InvalidArgumentException
	 * @api
	 */
	public function set($entryIdentifier, $data, array $tags = array(), $lifetime = NULL) {
		if (!$this->isValidEntryIdentifier($entryIdentifier)) {
			throw new \InvalidArgumentException('"' . $entryIdentifier . '" is not a valid cache entry identifier.', 1233057566);
		}
		if (!is_array($data)) {
			throw new InvalidDataException('Given data is of type "' . gettype($data) . '", but an array is expected for static file cache.', 1222808333);
		}
		foreach ($tags as $tag) {
			if (!$this->isValidTag($tag)) {
				throw new \InvalidArgumentException('"' . $tag . '" is not a valid tag for a cache entry.', 1233057512);
			}
		}

		$this->backend->set($entryIdentifier, $data, $tags, $lifetime);
	}

}