<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DomainService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;

class DomainController
{
    /**
     * @Inject()
     * @var DomainService
     */
    protected DomainService $domainService;


    /**
     * @Inject()
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;


    /**
     * Get all domains
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return array
     */
    public function getDomains(RequestInterface $request, ResponseInterface $response): array
    {
        $domains = $this->domainService->getDomains();
        return rjson('get all domains', 200, $domains);
    }

    /**
     * Get domain information
     * @param RequestInterface $request
     * @param string $name
     * @return array
     */
    public function getDomain(RequestInterface $request, string $name): array
    {
        $domains = $this->domainService->getDomains();
        $domains = array_column($domains, null, 'domain');
        return rjson('domain information', 200, $domains[$name] ?? null);
    }

    /**
     * Write domain
     *
     * @param RequestInterface $request
     * @param string $name
     * @return array
     */
    public function writeDomain(RequestInterface $request, string $name): array
    {
        $show = boolval($request->input('show', true));
        $domain = strval($request->input('domain', true));
        if (!$name) {
            return rjson('domain invalid', 500);
        }
        if ($this->domainService->writeDomain($name, [
            'domain' => $domain,
            'show'   => $show,
        ])) {
            $this->domainService->AsyncUpdateDomain($domain);
            return rjson('successfully written domain configuration', 200);
        }
        return rjson('Failed to write domain configuration', 500);
    }

    /**
     * remove domain from queue
     *
     * @param RequestInterface $request
     * @param string $name
     * @return array
     */
    public function removeDomain(RequestInterface $request, string $name): array
    {
        if (!$name) {
            return rjson('domain invalid', 500);
        }

        if ($this->domainService->removeDomain($name)) {
            return rjson('successfully removed domain', 200);
        }
        return rjson('Failed to remove domain configuration', 500);
    }

    /**
     * Domain name refresh
     *
     * @param RequestInterface $request
     * @return array
     */
    public function flushDomain(RequestInterface $request): array
    {
        $domain = $request->input('domain', null);
        if (!($domain === null || is_string($domain))) {
            return rjson('domain invalid', 500);
        }
        $this->domainService->AsyncUpdateDomain($domain);
        return rjson('Domain name refresh successfully', 200);
    }
}
