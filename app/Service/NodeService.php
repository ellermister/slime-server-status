<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2022/5/29
 * Time: 14:19
 */

namespace App\Service;


use App\Exception\LogicException;
use Hyperf\Redis\Redis;
use Hyperf\Di\Annotation\Inject;

class NodeService
{
    /**
     * @Inject
     * @var Redis
     */
    protected $redis;

    const NODE_HASH_KEY = 'nodes';
    const NODE_STATUS_HASH_KEY = 'nodes_status';

    /**
     * 验证节点权限
     *
     * @param string $nodeId
     * @param string $key
     * @return bool
     */
    public function verifyAuth(string $nodeId,string $key): bool
    {
        $nodeStr = $this->redis->hGet(self::NODE_HASH_KEY,$nodeId);
        if(!$nodeStr){
            return false;
        }

        $node = json_decode($nodeStr,true);
        if(is_array($node)){
            return $node['key'] == trim($key);
        }
        return false;
    }

    /**
     * 判断节点是否存在
     *
     * @param string $nodeId
     * @return bool
     */
    public function hasNode(string $nodeId): bool
    {
        return $this->redis->hExists(self::NODE_HASH_KEY, $nodeId);
    }

    /**
     * 新增节点
     *
     * @param string $nodeId
     * @param string $name
     * @param string $ip
     * @param string $key
     * @return bool
     */
    public function addNode(string $nodeId, string $name, string $ip, string $key): bool
    {
        if(!$this->verifyIDFormat($nodeId)){
            return false;
        }

        if($this->hasNode($nodeId)){
            return false;
        }

        $data = json_encode([
            'node_id' => $nodeId,
            'name'    => $name,
            'ip'      => $ip,
            'key'     => $key
        ]);
        return $this->redis->hSet(self::NODE_HASH_KEY, $nodeId, $data);
    }

    /**
     * 更新节点状态
     *
     * @param string $nodeId
     * @param string $status
     * @return bool
     */
    public function updateStatus(string $nodeId, string $status): bool
    {
        return $this->redis->hSet(self::NODE_STATUS_HASH_KEY, $nodeId, $status) !== false;
    }

    /**
     * 获取所有节点状态
     *
     * @return array
     */
    public function getAllNodeStatus(): array
    {
        $list = [];
        $nodesStatus = $this->redis->hGetAll(self::NODE_STATUS_HASH_KEY);
        foreach ($nodesStatus as $key => $raw){
            $list[$key] = json_decode($raw, JSON_UNESCAPED_UNICODE);
        }
        return $list;
    }

    /**
     * 获取所有节点
     *
     * @return array
     */
    public function getAllNode(): array
    {
        $nodes =  $this->redis->hGetAll(self::NODE_HASH_KEY);
        $list = [];
        foreach ($nodes as $node){
            $list[] = json_decode($node, true);
        }
        return $list;
    }

    /**
     * 获取节点信息
     *
     * @param string $nodeId
     * @return array
     */
    public function getNode(string $nodeId): array
    {
        $res = $this->redis->hGet(self::NODE_HASH_KEY, $nodeId);
        if($res && $node = json_decode($res, true)){
            if(is_array($node)){
                return $node;
            }
        }
        throw new LogicException("node id invalid", 404);
    }

    /**
     * 更新节点信息
     *
     * @param string $nodeId
     * @param array $nodeArr
     * @return bool
     */
    public function updateNode(string $nodeId, array $nodeArr): bool
    {
        if(!$this->verifyIDFormat($nodeId)){
            return false;
        }

        if(!$this->hasNode($nodeId)){
            return false;
        }

        $data = json_encode([
            'node_id' => $nodeId,
            'name'    => $nodeArr['name'] ?? '',
            'ip'      => $nodeArr['ip'] ?? '',
            'key'     => $nodeArr['key'] ?? '',
        ]);
        return $this->redis->hSet(self::NODE_HASH_KEY, $nodeId, $data)!==false;
    }

    /**
     * 删除节点
     *
     * @param string $nodeId
     * @return bool
     */
    public function deleteNode(string $nodeId): bool
    {
        if(!$this->verifyIDFormat($nodeId)){
            return false;
        }

        if(!$this->hasNode($nodeId)){
            return true;
        }

        return $this->redis->hDel(self::NODE_HASH_KEY, $nodeId)!==false;
    }


    /**
     * 验证ID格式
     *
     * @param string $id
     * @return bool
     */
    public function verifyIDFormat(string $id): bool
    {
        return (bool)preg_match('/^[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}$/', $id);
    }

    /**
     * 生成节点ID
     *
     * @return string
     */
    public function createNodeId(): string
    {
        $chars = md5(uniqid(mt_rand(), true));
        return substr ( $chars, 0, 8 ) . '-'
            . substr ( $chars, 8, 4 ) . '-'
            . substr ( $chars, 12, 4 ) . '-'
            . substr ( $chars, 16, 4 ) . '-'
            . substr ( $chars, 20, 12 );
    }
}