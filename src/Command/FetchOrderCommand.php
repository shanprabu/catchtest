<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class FetchOrderCommand extends Command
{
    protected static $defaultName = "order:fetch";
    protected static $defaultDescription = "Fetch orders from jsonl file and generate CSV";

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln("Fetching orders");
        return Command::SUCCESS;
    }
}