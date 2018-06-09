<?php

declare(strict_types=1);

namespace Lifeworks\SwooleHttpServerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StopCommand extends Command
{
    protected $rootDir;
    private $swoolePidFileName;

    public function __construct(string $kernelProjectDirectory, string $swoolePidFileName)
    {
        $this->rootDir = $kernelProjectDirectory;
        $this->swoolePidFileName = $swoolePidFileName;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('swoole:stop')->setDescription('stop swoole http server');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $pidFile = $this->rootDir . '/var/' . $this->swoolePidFileName;

        if (false === is_dir($this->rootDir.'/var/')) {
            throw new \Exception('Directory does not exist');
        }

        if (!file_exists($pidFile)) {
            return;
        }

        $f = fopen($pidFile, 'r');
        $processId = fgets($f);
        fclose($f);

        exec("kill {$processId}");
        unlink($pidFile);
    }
}
