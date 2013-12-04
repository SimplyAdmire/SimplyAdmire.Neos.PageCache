<?php
namespace SimplyAdmire\Neos\PageCache\Service;

use TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class PageCacheService {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Cache\Frontend\StringFrontend
	 */
	protected $pageCache;

	/**
	 * Clear page cache
	 *
	 * @return void
	 */
	public function clearCache() {
		$this->systemLogger->log('Page cache is cleared');
		$this->pageCache->flush();
	}
}