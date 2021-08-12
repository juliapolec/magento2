<?php
declare(strict_types=1);

namespace Academy\TurboModule\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



class AddCommand extends Command
{

    protected function configure()
    {

        parent::configure();
        $this->setName('academy:add:command');
        $this->setDescription('Academy training add example.');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Hello world!</info>');
    }
}

