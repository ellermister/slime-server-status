<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2022/5/29
 * Time: 14:03
 */

namespace App\Controller;

use App\Service\NodeService;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Engine\WebSocket\Opcode;
use Hyperf\Redis\Redis;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;
use Hyperf\WebSocketServer\Context;
use Hyperf\Di\Annotation\Inject;


class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{

    protected $connected = [];
    const WAIT_AUTH = 0;
    const AUTHED = 1;

    /**
     * @Inject
     * @var Redis
     */
    protected $redis;

    /**
     * @Inject
     * @var NodeService
     */
    protected $nodeService;

    public function onClose($server, int $fd, int $reactorId): void
    {
        // TODO: Implement onClose() method.
        Context::destroy('auth');
        Context::destroy('node_id');
        Context::destroy('client');
    }

    public function onMessage($server, Frame $frame): void
    {
        if($frame->opcode == Opcode::PING) {
            $server->push('', Opcode::PONG);
            return;
        }

        $clientInfo = $server->getClientInfo($frame->fd);
        $testData = json_encode(
            [
                'info' => json_decode(json_encode($clientInfo, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), true),
                'datetime' => date('Y-m-d H:i:s'),
                'client' => Context::get('client')
            ]
        );
        $this->redis->hSet('test_client',$clientInfo['remote_ip'], $testData);
        // TODO: Implement onMessage() method.
//        echo "收到消息:".$frame->fd.",msg:{$frame->data}".PHP_EOL;
        if(Context::get('auth') == self::WAIT_AUTH){
            if(preg_match('/auth:([a-zA-Z0-9\-]{36}),key:([A-Za-z0-9]{10,40})/', $frame->data, $matches)){
                $nodeId = $matches[1];
                $key = $matches[2];
                if($this->nodeService->verifyAuth($nodeId, $key)){
                    Context::set('auth', self::AUTHED);
                    Context::set('node_id', $nodeId);
                    $server->push($frame->fd, 'authed');
                }else{
                    $retry = intval(Context::getOrSet('retry',1));
                    if($retry > 3){
                        $server->close($frame->fd, 'Too many wrong key');
                    }else{
                        Context::set('retry', intval($retry + 1));
                        $server->push($frame->fd, 'wrong key');
                        $server->push($frame->fd, 'auth');
                    }
                }
            }else{
                $server->close($frame->fd, 'invalid format');
            }
        }else{
            $this->nodeService->updateStatus(Context::get('node_id'),$frame->data);
        }
    }

    public function onOpen($server, Request $request): void
    {
        // TODO: Implement onOpen() method.
//        echo "收到连接:".$request->fd.PHP_EOL;
        Context::set('auth', self::WAIT_AUTH);
        Context::set('client', $request->header);
        $server->push($request->fd, 'auth');
    }



}