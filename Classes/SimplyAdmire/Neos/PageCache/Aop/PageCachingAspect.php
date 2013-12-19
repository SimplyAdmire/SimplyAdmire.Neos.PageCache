<?php
namespace SimplyAdmire\Neos\PageCache\Aop;

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;
use TYPO3\Flow\Http\Request as HttpRequest;
use TYPO3\Flow\Mvc\ActionRequest;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Mvc\RequestInterface;
use TYPO3\Flow\Utility\Files;

/**
 * An aspect which provides a simple page cache
 *
 * @Flow\Aspect
 */
class PageCachingAspect {

	/**
	 * The flash messages. Use $this->flashMessageContainer->addMessage(...) to add a new Flash
	 * Message.
	 *
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Mvc\FlashMessageContainer
	 */
	protected $flashMessageContainer;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Authentication\AuthenticationManagerInterface
	 */
	protected $authenticationManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Cache\Frontend\StringFrontend
	 */
	protected $pageCache;

	/**
	 * @var array
	 */
	protected $staticCachableContentTypes = array(
		'text/html'
	);

	/**
	 * @param JoinPointInterface $joinPoint
	 * @return void
	 * @Flow\After("setting(SimplyAdmire.Neos.PageCache.cache.enable) && method(TYPO3\Neos\Controller\Frontend\NodeController->showAction())")
	 */
	public function handleNoCacheProperty(JoinPointInterface $joinPoint) {
		$node = $joinPoint->getMethodArgument('node');

		if ($node->getProperty('noCache')) {
			$joinPoint->getProxy()->getControllerContext()->getResponse()->setHeader('X-Flow-PageCache', 'pass (ignore)');
		}
	}

	/**
	 * Implements a simple request cache
	 *
	 * @param \TYPO3\Flow\Aop\JoinPointInterface $joinPoint
	 * @return void
	 * @Flow\Around("setting(SimplyAdmire.Neos.PageCache.cache.enable) && method(TYPO3\Flow\Mvc\Dispatcher->dispatch())")
	 */
	public function dispatchAdvice(JoinPointInterface $joinPoint) {
		/** @var \TYPO3\Flow\Mvc\RequestInterface $request */
		$request = $joinPoint->getMethodArgument('request');
		/** @var \TYPO3\Flow\Http\Response $response */
		$response = $joinPoint->getMethodArgument('response');

		if ($request instanceof \TYPO3\Flow\Cli\Request) {
			$joinPoint->getAdviceChain()->proceed($joinPoint);
			return;
		}

		if ($request->getControllerObjectName() === 'TYPO3\Neos\Controller\Frontend\NodeController' && !$this->authenticationManager->isAuthenticated()) {
			$httpRequest = $request->getHttpRequest();

			if (!$httpRequest->isMethodSafe()) {
				$joinPoint->getAdviceChain()->proceed($joinPoint);
				$response->setHeader('X-Flow-PageCache', 'pass (unsafe)');
				return;
			}

			$cacheIdentifier = sha1($httpRequest->getUri());
			$joinPoint->getAdviceChain()->proceed($joinPoint);

			if (!$response->hasHeader('X-Flow-PageCache') || $response->getHeader('X-Flow-PageCache') !== 'pass (ignore)') {
				$cacheEntry = array(
					'host' => $httpRequest->getUri()->getHost(),
					'path' => $httpRequest->getUri()->getPath(),
					'format' => $request->getFormat(),
					'content' => $response->getContent()
				);

				$this->pageCache->set($cacheIdentifier, $cacheEntry);
				$response->setHeader('X-Flow-PageCache', 'store');
			}
		} else {
			$joinPoint->getAdviceChain()->proceed($joinPoint);
			$response->setHeader('X-Flow-PageCache', 'pass (ignore)');
		}
	}
}
