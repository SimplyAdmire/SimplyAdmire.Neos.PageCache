<?php
namespace SimplyAdmire\Neos\PageCache\Command;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;

/**
 * @Flow\Scope("singleton")
 */
class CleanCommandController extends CommandController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Cache\Frontend\StringFrontend
	 */
	protected $pageCache;

	/**
	 * Clean page cache
	 *
	 * @return void
	 */
	public function cacheCommand() {
		$this->outputLine('Cleaning the page cache');
		$this->pageCache->flush();
	}

}
