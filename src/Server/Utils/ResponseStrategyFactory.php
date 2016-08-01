<?php
namespace Mcustiel\Phiremock\Server\Utils;

use Mcustiel\Phiremock\Domain\Expectation;
use Mcustiel\Phiremock\Server\Utils\Strategies\ProxyResponseStrategy;
use Mcustiel\Phiremock\Server\Utils\Strategies\HttpResponseStrategy;
use Mcustiel\DependencyInjection\DependencyInjectionService;
use Mcustiel\Phiremock\Server\Utils\Strategies\RegexResponseStrategy;
use Mcustiel\Phiremock\Server\Config\Matchers;

class ResponseStrategyFactory
{
    /**
     * @var \Mcustiel\DependencyInjection\DependencyInjectionService
     */
    private $diService;

    public function __construct(DependencyInjectionService $dependencyService)
    {
        $this->diService = $dependencyService;
    }

    /**
     * @param \Mcustiel\Phiremock\Domain\Expectation $expectation
     *
     * @return \Mcustiel\Phiremock\Server\Utils\Strategies\ResponseStrategyInterface
     */
    public function getStrategyForExpectation(Expectation $expectation)
    {
        if (!empty($expectation->getProxyTo())) {
            return $this->diService->get(ProxyResponseStrategy::class);
        }
        if ($expectation->getRequest()->getBody() && $expectation->getRequest()->getBody()->getMatcher() == Matchers::MATCHES
            || $expectation->getRequest()->getUrl() && $expectation->getRequest()->getUrl()->getMatcher() == Matchers::MATCHES) {
            return $this->diService->get(RegexResponseStrategy::class);
        }
        return $this->diService->get(HttpResponseStrategy::class);
    }
}
