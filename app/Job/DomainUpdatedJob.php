<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2022/9/6
 * Time: 23:25
 */

namespace App\Job;

use App\Service\DomainService;
use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;

class DomainUpdatedJob extends Job
{
    public $domain;

    public function __construct($domain = null)
    {
        $this->domain = $domain;
    }

    public function handle()
    {
        if($this->domain === null){
            ApplicationContext::getContainer()->get(DomainService::class)->scheduledUpdateDomain();
        }else{
            ApplicationContext::getContainer()->get(DomainService::class)->scheduledUpdateDomain([$this->domain]);
        }
    }

}