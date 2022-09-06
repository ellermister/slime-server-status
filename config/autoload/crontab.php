<?php

use Hyperf\Crontab\Crontab;

return [
    // 是否开启定时任务
    'enable' => true,

    'crontab' => [
        (new Crontab())->setName('domain-whois-update')
            ->setType('command')
            ->setRule('0 1 * * *')->setCallback([
                'command' => 'domain:update:whois',
            ])
        ,
    ]
];