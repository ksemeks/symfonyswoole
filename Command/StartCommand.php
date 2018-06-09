<?php

declare(strict_types=1);

namespace Lifeworks\SwooleHttpServerBundle\Command;

use Lifeworks\SwooleHttpServerBundle\Http\Http;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class StartCommand extends Command
{
    /** @var $kernel Kernel */
    protected $kernel;
    /** @var $server Server */
    protected $server;
    protected $env = 'prod';
    protected $debug = false;
    protected $address;
    protected $processIdFile;
    protected $rootDir;
    private $swoolePidFileName;

    public function __construct(string $kernelProjectDirectory, string $swoolePidFileName, Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->rootDir = $kernelProjectDirectory;
        $this->swoolePidFileName = $swoolePidFileName;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('swoole:start')
            ->setDescription('run swoole http server in background');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $cacheDir = $this->rootDir . '/var'. $this->swoolePidFileName;
        $this->processIdFile = $cacheDir;

        $this->server = new Server('127.0.0.1', 9501);
        $this->server->set([
            'daemonize' => 4,
        ]);
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('request', [$this, 'onRequest']);
        $this->server->start();
    }

    public function onRequest(SwooleRequest $swooleRequest, Response $swooleResponse): void
    {
        $static = $this->rootDir.'/public'.$swooleRequest->server['path_info'];

        if ('/' != $swooleRequest->server['path_info'] && file_exists($static)) {
            $ext = pathinfo($static, PATHINFO_EXTENSION);
            $swooleResponse->header('Content-Type', sprintf('text/%s', $ext));
            $swooleResponse->end(file_get_contents($static));

            return;
        }

        /** @var Request $symfonyRequest */
        $symfonyRequest = Http::createSymfonyRequest($swooleRequest);
        $symfonyResponse = $this->kernel->handle($symfonyRequest);
        $swooleResponse->end(Http::createSwooleResponse($swooleResponse, $symfonyResponse));
        $this->kernel->terminate($symfonyRequest, $symfonyResponse);
    }

    public function onStart(Server $server): void
    {
        if (false === is_dir($this->rootDir.'/var/')) {
            throw new \Exception('Directory does not exist');
        }

        $pidFile = $this->rootDir . '/var/' . $this->swoolePidFileName;
        $pidFile = fopen($pidFile, 'w');

        if (false === $pidFile) {
            throw new \Exception('File is not writable');
        }

        fwrite($pidFile, (string) $this->server->master_pid);
        fclose($pidFile);
    }
}
