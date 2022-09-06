<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Cases;

use App\Service\DomainService;
use Hyperf\Utils\ApplicationContext;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class DomainServiceTest extends HttpTestCase
{

    /**
     * @var DomainService|mixed
     */
    protected $domainService;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->domainService = ApplicationContext::getContainer()->get(DomainService::class);
    }

    /**
     * test domain whois
     */
    public function testDomainWhois()
    {
        $information = $this->domainService->whoisDomain('google.com');
        $this->assertTrue(isset($information['expirationDate']));
    }
}
