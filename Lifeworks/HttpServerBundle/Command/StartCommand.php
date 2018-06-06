<?php
namespace Swoole\HttpServerBundle\Command;


use Swoole\Http\Response;
use Swoole\Http\Server;
use Swoole\HttpServerBundle\Http\Http;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class StartCommand extends ContainerAwareCommand
{
	/** @var $kernel Kernel */
	protected $kernel;
	/** @var $server Server */
	protected $server;
	protected $env    = 'prod';
	protected $debug  = false;
	protected $address;
    protected $pid_file;

	protected function configure()
	{
		$this->setName('swooleserver:start')
			->setDescription('run swoole http server in background')
			->setDefinition(array(
				new InputOption('host', null, InputOption::VALUE_OPTIONAL, 'Host for server', '127.0.0.1'),
				new InputOption('port', null, InputOption::VALUE_OPTIONAL, 'Port for server', 2345),
			));
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$root_dir = $this->getContainer()->getParameter('kernel.root_dir');
		$web_dir = $root_dir.'/../web'.'/swoole_http_server.pid';
		$this->pid_file = $web_dir;

		$this->init( $input,  $output);
		$this->server = new Server($input->getOption('host'),$input->getOption('port'));
		$this->server->set([
			'daemonize'=>4,
		]);
		$this->server->on('start',[$this,'onStart']);
		$this->server->on('Connect',[$this,'onConnect']);
		$this->server->on('start',[$this,'onStart']);
		$this->server->on('request',[$this,'onRequest']);
		$this->server->on('shutdown',[$this,'onShutdown']);
		$this->server->start();

	}

	public function onConnect(Server $server,$fd,$reactorThreadId)
	{
	}

	public function onRequest(\Swoole\Http\Request $swRequest,Response $swResponse)
	{
		$root_dir = $this->getContainer()->getParameter('kernel.root_dir');
		$static = $root_dir.'/../public'.$swRequest->server['path_info'];
		if ($swRequest->server['path_info']!='/' && file_exists($static)) {
			$ext = pathinfo($static, PATHINFO_EXTENSION);
			$swResponse->header('Content-Type', sprintf('text/%s', $ext));
			$swResponse->end(file_get_contents($static));
			return;
		}
		$kernel = $this->getContainer()->get('kernel');
		/** @var Request $sfRequest */
		$sfRequest = Http::createSfRequest($swRequest);
		$sfResponse = $kernel->handle($sfRequest);
		$swResponse->end(Http::createSwResponse($swResponse,$sfResponse));
		$kernel->terminate($sfRequest,$sfResponse);
	}

	public function onStart(Server $server)
	{
        $rootDirectory = $this->getContainer()->getParameter('kernel.root_dir');
        $pidFile = $rootDirectory.'/../var'.'/swoole_http_server.pid';
        $pidFile = fopen($pidFile, 'w');
        fwrite($pidFile, (string) $this->server->master_pid);
        fclose($pidFile);
	}

	public function onShutdown(Server $server)
	{
		unlink($this->pid_file);
	}
}
