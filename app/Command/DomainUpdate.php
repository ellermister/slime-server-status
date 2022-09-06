<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DomainService;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * @Command
 */
#[Command]
class DomainUpdate extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;


    /**
     * @Inject()
     * @var Redis
     */
    protected Redis $redis;

    /**
     * @Inject()
     * @var DomainService
     */
    protected DomainService $domainService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('domain:update:whois');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Update domain whois information');
    }

    public function handle()
    {
        $this->domainService->scheduledUpdateDomain();
    }
}
