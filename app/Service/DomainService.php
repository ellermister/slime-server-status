<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2022/9/6
 * Time: 13:32
 */

namespace App\Service;

use App\Exception\LogicException;
use App\Job\DomainUpdatedJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\Redis\Redis;
use Iodev\Whois\Factory;
use Hyperf\Di\Annotation\Inject;

class DomainService
{

    /**
     * @Inject
     * @var Redis
     */
    protected Redis $redis;

    const DOMAIN_DB_PREFIX = 'domain';
    const DOMAIN_WHOIS_DB_PREFIX = 'domain_whois';


    /**
     * @var DriverInterface $driver
     */
    protected DriverInterface $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('default');
    }


    /**
     * Whois domain
     *
     * @param string $domain
     * @return array
     * @throws \Iodev\Whois\Exceptions\ConnectionException
     * @throws \Iodev\Whois\Exceptions\ServerMismatchException
     * @throws \Iodev\Whois\Exceptions\WhoisException
     */
    public function whoisDomain(string $domain): array
    {
        $whois = Factory::get()->createWhois();

        if ($response = $whois->loadDomainInfo($domain)) {
            return $response->toArray();
        }
        throw new LogicException("Whois domain fail, $domain", 500);
    }

    /**
     * write domain to queue
     *
     * @param string $domain
     * @param array $param
     * @return bool
     */
    public function writeDomain(string $domain, array $param): bool
    {
        $originDomain = trim($domain);
        $newDomain = trim($param['domain']);
        $data = [
            'show'   => $param['show'],
            'domain' => $newDomain,
        ];
        if ($this->redis->hExists(self::DOMAIN_DB_PREFIX, $originDomain)) {
            $this->removeDomain($domain);
        }
        return $this->redis->hSet(self::DOMAIN_DB_PREFIX, $newDomain, json_encode($data, JSON_UNESCAPED_UNICODE)) !== false;
    }

    /**
     * Remove domain from queue
     *
     * @param string $domain
     * @return bool
     */
    public function removeDomain(string $domain): bool
    {
        $result = $this->redis->hDel(self::DOMAIN_DB_PREFIX, trim($domain)) !== false;
        $this->redis->hDel(self::DOMAIN_WHOIS_DB_PREFIX, trim($domain));
        return $result;
    }

    /**
     * Get domain
     * @return array
     */
    public function getDomains(): array
    {
        $list = [];
        if ($data = $this->redis->hGetAll(self::DOMAIN_DB_PREFIX)) {
            $whoisDomains = $this->getDomainsWhoisInfo();
            foreach ($data as $domain => $value) {
                $row = json_decode($value, true);
                if ($row) {
                    $row['whois'] = $whoisDomains[$domain] ?? null;
                }
                $list[] = $row;
            }
        }
        return $list;
    }


    /**
     * get whois info of domain
     *
     * @return array
     */
    public function getDomainsWhoisInfo(): array
    {
        $data = $this->redis->hGetAll(self::DOMAIN_WHOIS_DB_PREFIX);
        $list = [];
        if ($data) {
            foreach ($data as $domain => $value) {
                $list[$domain] = json_decode($value, true);
            }
        }
        return $list;
    }

    /**
     * Set domain whois info
     *
     * @param string $domain
     * @param array $info
     * @return bool
     */
    public function setDomainWhoisInfo(string $domain, array $info): bool
    {
        $info['updated_at'] = date('Y-m-d H:i:s');
        if (isset($info['creationDate']) && is_integer($info['creationDate'])) {
            $info['creationDateStr'] = date('Y-m-d H:i:s', $info['creationDate']);
        }

        if(isset($info['expirationDate']) && is_integer($info['expirationDate'])){
            $info['expirationDateStr'] = date('Y-m-d H:i:s', $info['expirationDate']);
        }

        if(isset($info['updatedDate'])  && is_integer($info['updatedDate'])){
            $info['updatedDateStr'] = date('Y-m-d H:i:s', $info['updatedDate']);
        }

        return $this->redis->hSet(self::DOMAIN_WHOIS_DB_PREFIX, trim($domain), json_encode($info, JSON_UNESCAPED_UNICODE)) !== false;
    }

    /**
     * Schedule update domain
     *
     * @param array|null $only
     * @throws \Iodev\Whois\Exceptions\ConnectionException
     * @throws \Iodev\Whois\Exceptions\ServerMismatchException
     * @throws \Iodev\Whois\Exceptions\WhoisException
     */
    public function scheduledUpdateDomain(array $only = null)
    {
        $domains = $this->getDomains();
        foreach ($domains as $value) {
            if(($only && in_array($value['domain'], $only)) || !$only){
                $info = $this->whoisDomain($value['domain']);
                $this->setDomainWhoisInfo($value['domain'], $info);
            }
        }
    }

    /**
     * Async update domain
     *
     * @param $domain
     */
    public function AsyncUpdateDomain($domain)
    {
        $this->driver->push(new DomainUpdatedJob($domain));
    }

}