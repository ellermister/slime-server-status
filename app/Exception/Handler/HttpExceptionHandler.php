<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2022/6/5
 * Time: 2:45
 */

namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    public function __construct(StdoutLoggerInterface $logger, FormatterInterface $formatter)
    {
        $this->logger = $logger;
        $this->formatter = $formatter;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->debug($this->formatter->format($throwable));

        $this->stopPropagation();
        if ($throwable->getStatusCode() == 404) {
            return $response->withStatus(200)->withBody(new SwooleStream(file_get_contents(BASE_PATH.'/public/dist/index.html')));
        }
        return $response->withStatus($throwable->getStatusCode())->withBody(new SwooleStream($throwable->getMessage()));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof HttpException;
    }

}