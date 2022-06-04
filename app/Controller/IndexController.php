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

namespace App\Controller;

use App\Service\LoginService;
use App\Service\NodeService;
use Hyperf\Di\Annotation\Inject;

class IndexController extends AbstractController
{

    /**
     * @Inject
     * @var NodeService
     */
    protected $nodeService;

    /**
     * @Inject
     * @var LoginService
     */
    protected $loginService;

    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method'  => $method,
            'message' => "Hello {$user}.",
        ];
    }

    public function createToken(): array
    {
        return [
            'token' => $this->loginService->createToken(),
        ];
    }


    /**
     * 公开节点状态
     *
     * @return array[]
     */
    public function nodeStatus(): array
    {
        $nodes = $this->nodeService->getAllNode();
        $nodes  = array_column($nodes, null, 'node_id');

        $nodeStatus = $this->nodeService->getAllNodeStatus();
        $statusFormat = [];
        foreach ($nodeStatus as $id => $status) {
            if(isset($nodes[$id])){
                $statusFormat[] = [
                    'stat' => $status,
                    'id'   => $id,
                    'name' => $nodes[$id]['name']
                ];
            }
        }
        return rjson('nodes status', 200, $statusFormat);
    }

    public function createNodeId(): array
    {
        return [
            'id' => $this->nodeService->createNodeId()
        ];
    }
}
