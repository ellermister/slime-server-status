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
namespace App\Exception\Handler;

use App\Exception\LogicException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());

        if($throwable instanceof LogicException){
            return $response->withStatus(500)->withBody(new SwooleStream(
                json_encode(rjson($throwable->getMessage(),$throwable->getCode()))
            ));
        }
        return $response->withHeader('Server', 'Hyperf')->withStatus(500)->withBody(new SwooleStream(
            json_encode(rjson('Internal Server Error.',500))
        ));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
