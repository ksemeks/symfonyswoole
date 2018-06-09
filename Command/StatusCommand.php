<?php

declare(strict_types=1);

namespace Lifeworks\SwooleHttpServerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends Command
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
        $this->setName('swoole:status')->setDescription('check swoole http server status');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $pidFile = $this->rootDir . '/var/' . $this->swoolePidFileName;

        if (!file_exists($pidFile)) {
            return;
        }

        $f = fopen($pidFile, 'r');

        if (false === $f) {
            throw new \Exception('File is not readable');
        }

        $processId = fgets($f);
        fclose($f);

        if ($processId) {
            $output->writeln('swoole http server is running, pid is '.$processId);
        } else {
            $output->writeln('swoole http server is not run');
        }
    }
}
