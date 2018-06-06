<?php

namespace Swoole\HttpServerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReloadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('swooleserver:reload')->setDescription('reload swoole http server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root_dir = $this->getContainer()->getParameter('kernel.root_dir');
        $pid_file =   $root_dir.'/../var'.'/swoole_http_server.pid';
        if(!file_exists($pid_file))
        {
            return false;
        }
        $f= fopen($pid_file,"r");
        $pid = fgets($f);
        fclose($f);
        exec("kill -USR1 {$pid}");
    }
}
