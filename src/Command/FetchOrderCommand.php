<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class FetchOrderCommand extends Command
{
    protected static $defaultName = "order:fetch";
    protected static $defaultDescription = "Fetch orders from jsonl file and generate CSV";

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln("Downloading data file from URL");
        return Command::SUCCESS;
    }

    public function returnValue() : bool
    {
        return true;
    }

    private function getDataFromUrl() : bool
    {
        $fileSystem = new Filesystem();
        if(!$fileSystem->exists('storage')) {
            $fileSystem->mkdir('storage', 0700);
        }
        try {
            $fileSystem->dumpFile('storage/orders.jsonl', file_get_contents($_ENV['DATA_URL']));
            $response = true;
        }
        catch(IOException $e) {
            echo "Error downloading file - " . $e->getMessage();
            $response = false;
        }
        return $response;
    }
}