<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2022/5/29
 * Time: 18:14
 */

namespace App\Service;

use Hyperf\Redis\Redis;
use Hyperf\Di\Annotation\Inject;

class LoginService
{
    /**
     * @Inject
     * @var Redis
     */
    protected $redis;

    const AUTH_PASSWORD = "auth_password";
    const AUTH_TOKEN = "auth_token";

    /**
     * 设置密码
     *
     * @param string $password
     * @return bool
     */
    public function setPassword(string $password): bool
    {
        return $this->redis->set(self::AUTH_PASSWORD, password_hash($password, PASSWORD_BCRYPT));
    }

    /**
     * 获取密码hash值
     *
     * @return string
     */
    public function getPasswordHash(): string
    {
        return $this->redis->get(self::AUTH_PASSWORD) ?? '';
    }

    /**
     * 验证密码
     *
     * @param string $input
     * @return bool
     */
    public function verifyPassword(string $input): bool
    {
        return password_verify($input, $this->redis->get(self::AUTH_PASSWORD));
    }

    /**
     * 是否设置过密码
     *
     * @return bool|int
     */
    public function hasPassword()
    {
        return $this->redis->exists(self::AUTH_PASSWORD);
    }

    /**
     * 创建TOKEN
     *
     * @return false|string
     */
    public function createToken()
    {
        $expired_time = time() + 365 * 86400;
        $pass = md5($this->getPasswordHash());
        return openssl_encrypt('expired_at:' . $expired_time, "AES-128-ECB", $pass);
    }

    /**
     * 验证 token 有效性
     *
     * @param string $text
     * @return bool
     */
    public function verifyToken(string $text): bool
    {
        $pass = md5($this->getPasswordHash());
        $decrypted = openssl_decrypt($text, "AES-128-ECB", $pass);
        list($_, $expired_time) = explode(':', $decrypted);
        return $expired_time > time();
    }

}