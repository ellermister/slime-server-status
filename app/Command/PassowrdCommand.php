<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\LoginService;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Hyperf\Di\Annotation\Inject;

/**
 * @Command
 */
#[Command]
class PassowrdCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var LoginService
     */
    protected $loginService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('password:set');
    }

    public function configure()
    {
        parent::configure();
        $this->addArgument('password', InputArgument::REQUIRED, 'password');
        $this->setDescription('Set login password for slime status');
    }

    public function handle()
    {
        $password = $this->input->getArgument('password');

        if(!preg_match('/^[a-zA-Z0-9]{8,}$/', $password)){
            $this->error('password is too short!');
        }else{
            if($this->loginService->setPassword($password)){
                $this->info(sprintf("New password is:%s", $password));
            }else{
                $this->info(sprintf("password set fail, please check redis"));
            }
        }
    }
}
