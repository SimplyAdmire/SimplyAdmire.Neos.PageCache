<?php
namespace SimplyAdmire\Neos\PageCache;

use TYPO3\Flow\Package\Package as BasePackage;

class Package extends BasePackage {

	/**
	 * Boot the package. We wire some signals to slots here.
	 *
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(\TYPO3\Flow\Core\Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		$dispatcher->connect(
			'TYPO3\Neos\Service\PublishingService', 'nodePublished',
			'SimplyAdmire\Neos\PageCache\Service\PageCacheService', 'clearCache'
		);
	}
}
