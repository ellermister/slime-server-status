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
use Hyperf\HttpServer\Contract\RequestInterface;

class ManagerController extends AbstractController
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

    /**
     * 登录
     *
     * @return array
     */
    public function login(): array
    {
        $password = $this->request->input('password', '');
        if ($this->loginService->verifyPassword($password)) {
            return rjson('ok', 200, [
                'token' => $this->loginService->createToken(),
            ]);
        }
        return rjson('password error', 500);
    }

    /**
     * 登录
     *
     * @return array
     */
    public function setPassword(): array
    {
        $password = $this->request->input('password', '');
        if ($this->loginService->setPassword($password)) {
            return rjson('ok', 200, [
                'token' => $this->loginService->createToken(),
            ]);
        }
        return rjson('set password fail', 500);
    }

    /**
     * 获取节点列表
     *
     * @return array
     */
    public function getNodes(): array
    {
        return rjson('ok', 200, $this->nodeService->getAllNode());
    }

    /**
     * 获取节点信息
     *
     * @param string $id
     * @return array
     */
    public function getNode(string $id): array
    {
        return rjson('ok', 200, $this->nodeService->getNode($id));
    }

    /**
     * 更新节点信息
     *
     * @param RequestInterface $request
     * @param string $id
     * @return array
     */
    public function updateNode(RequestInterface $request, string $id): array
    {
        if ($this->nodeService->updateNode($id, [
                'name' => $request->input('name'),
                'ip'   => $request->input('ip'),
                'key'  => $request->input('key'),
            ]) !== false) {
            return rjson('ok', 200);
        } else {
            return rjson('update node fail', 500);
        }
    }


    /**
     * @param RequestInterface $request
     * @return array
     */
    public function addNode(RequestInterface $request): array
    {
        $nodeId = $request->input('node_id');
        $name = $request->input('name');
        $ip = $request->input('ip');
        $key = $request->input('key');
        if($this->nodeService->addNode($nodeId, $name, $ip, $key)){
            return rjson('ok',200);
        }else{
            return rjson('add node fail', 500);
        }
    }

    /**
     * 删除节点
     *
     * @param string $id
     * @return array
     */
    public function deleteNode(string $id): array
    {
        if($this->nodeService->deleteNode($id)){
            return rjson('ok',200);
        }else{
            return rjson('delete node fail', 500);
        }
    }


}
