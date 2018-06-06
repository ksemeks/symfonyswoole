<?php

namespace Swoole\HttpServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StopCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('swooleserver:stop')->setDescription('stop swoole http server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rootDirectory = $this->getContainer()->getParameter('kernel.root_dir');
        $pidFile = $rootDirectory.'/../public'.'/swoole_http_server.pid';

        if (!file_exists($pidFile)) {
            return false;
        }

        $f = fopen($pidFile, 'r');
        $processId = fgets($f);
        fclose($f);

        exec("kill {$processId}");
        unlink($pidFile);
    }
}
