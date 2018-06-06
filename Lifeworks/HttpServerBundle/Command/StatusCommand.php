<?php
namespace Swoole\HttpServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('swooleserver:status')->setDescription('check swoole http server status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rootDirectory = $this->getContainer()->getParameter('kernel.root_dir');
        $pidFile = $rootDirectory . '/../public' . '/swoole_http_server.pid';

        if (!file_exists($pidFile)) {
            return false;
        }

        $f = fopen($pidFile, 'r');
        $processId = fgets($f);
        fclose($f);

        if ($processId) {
            $output->writeln('swoole http server is running, pid is ' . $processId);
        } else {
            $output->writeln('swoole http server is not run');
        }
    }
}
