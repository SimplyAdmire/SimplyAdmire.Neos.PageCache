<?php
namespace SimplyAdmire\Neos\PageCache\Cache\Backend;

use TYPO3\Flow\Cache\Backend\SimpleFileBackend;
use TYPO3\Flow\Cache\Frontend\FrontendInterface;
use TYPO3\Flow\Utility\Exception;
use TYPO3\Flow\Utility\Files;
use TYPO3\Flow\Cache\Exception as CacheException;

class StaticFileBackend extends SimpleFileBackend {

	/**
	 * @var array
	 */
	protected $cachedFormats;

	/**
	 * @var boolean
	 */
	protected $sanitize;

	/**
	 * Sets a reference to the cache frontend which uses this backend and
	 * initializes the default cache directory.
	 *
	 * @param \TYPO3\Flow\Cache\Frontend\FrontendInterface $cache The cache frontend
	 * @return void
	 * @throws CacheException
	 */
	public function setCache(FrontendInterface $cache) {
		parent::setCache($cache);

		$cacheDirectory = FLOW_PATH_WEB . '_Resources/Cache/' . $this->cacheIdentifier;
		if (!is_writable($cacheDirectory)) {
			try {
				Files::createDirectoryRecursively($cacheDirectory);
			} catch (Exception $exception) {
				throw new CacheException('The cache directory "' . $cacheDirectory . '" could not be created.', 1387488916);
			}
		}
		if (!is_dir($cacheDirectory) && !is_link($cacheDirectory)) {
			throw new CacheException('The cache directory "' . $cacheDirectory . '" does not exist.', 1387488917);
		}
		if (!is_writable($cacheDirectory)) {
			throw new CacheException('The cache directory "' . $cacheDirectory . '" is not writable.', 1387488918);
		}

		$this->cacheDirectory = $cacheDirectory;
		$this->cacheEntryFileExtension = '';

		if ((strlen($this->cacheDirectory) + 23) > $this->environment->getMaximumPathLength()) {
			throw new CacheException('The length of the temporary cache path "' . $this->cacheDirectory . '" exceeds the maximum path length of ' . ($this->environment->getMaximumPathLength() - 23) . '. Please consider setting the temporaryDirectoryBase option to a shorter path. ', 1387488919);
		}
	}

	/**
	 * @param string $entryIdentifier
	 * @param array $data
	 * @param array $tags
	 * @param integer $lifetime
	 * @return void
	 */
	public function set($entryIdentifier, $data, array $tags = array(), $lifetime = NULL) {
		if (!in_array($data['format'], $this->getCachedFormats())) {
			return;
		}
		$path = $data['path'];
		if (strpos($path, '@') !== FALSE) {
			// Make sure we don't write a cache file for personal workspaces
			// This could happen if a Neos session is expired and the user clicks on a node in the node tree for example
			return;
		}
		$path = Files::concatenatePaths(array($this->getCacheDirectory(), $data['host'], $path, 'index.' . $data['format']));
		Files::createDirectoryRecursively(dirname($path));

		if ($this->isSanitize()) {
			$data['content'] = preg_replace('/(\t|\r|\n)/', '', $data['content']);
		}

		file_put_contents($path, $data['content']);
	}

	/**
	 * @param array $cachedFormats
	 * @return void
	 */
	public function setCachedFormats(array $cachedFormats) {
		$this->cachedFormats = $cachedFormats;
	}

	/**
	 * @return array
	 */
	public function getCachedFormats() {
		return $this->cachedFormats;
	}

	/**
	 * @param boolean $sanitize
	 * @return void
	 */
	public function setSanitize($sanitize) {
		$this->sanitize = $sanitize;
	}

	/**
	 * @return boolean
	 */
	public function isSanitize() {
		return $this->sanitize;
	}

}