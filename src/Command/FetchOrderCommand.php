<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use Symfony\Component\Serializer\Encoder\CsvEncoder;

use Rs\JsonLines\JsonLines;
use Carbon\Carbon;
use Symfony\Component\Console\Input\InputArgument;

class FetchOrderCommand extends Command
{
    protected static $defaultName = "order:fetch";
    protected static $defaultDescription = "Fetch orders from jsonl file and generate CSV";

    const DISTINCT_COUNT = 1;
    const TOTAL_COUNT = 2;

    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        parent::__construct();
    }

    protected function configure() : void
    {
        $this->addArgument('email',InputArgument::OPTIONAL,"Email address where the CSV should be mailed (optional):");
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln("Downloading data file from " . $_ENV['DATA_URL']);
        if($this->getDataFromUrl()) {
            $output->writeln([
                "Data file saved - storage/orders.jsonl",
                "Processing data file"
            ]);
            $data = $this->processFile();
            $output->writeln("Exporting CSV - storage/out.csv");
            $this->exportCsv($data);
        }
        if(!empty($input->getArgument('email'))) {
            $output->writeln("Sending CSV to " . $input->getArgument('email'));
            $this->sendEmail($input->getArgument('email'));
        }
        return Command::SUCCESS;
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

    private function processFile()
    {
        $jsonFile = (new JsonLines())->delineEachLineFromFile('storage/orders.jsonl');
        foreach ($jsonFile as $jsonLine) {
            $orderData = json_decode($jsonLine);
            if($this->getTotalOrderValue($orderData->items) > 0) {
                $orders[] = [
                    'order_id' => $orderData->order_id,
                    'order_datetime' => Carbon::parse($orderData->order_date)->toIso8601String(),
                    'total_order_value' => $this->getTotalOrderValue($orderData->items),
                    'average_unit_price' => $this->getAverageUnitPrice($orderData->items),
                    'distinct_unit_count' => $this->getItemCount($orderData->items, self::DISTINCT_COUNT),
                    'total_units_count' => $this->getItemCount($orderData->items, self::TOTAL_COUNT),
                    'customer_state' => $orderData->customer->shipping_address->state
                ];    
            }
        }
        return $orders;
    }

    private function getTotalOrderValue(array $items) : float
    {
        $orderValue = 0;
        foreach($items as $item)
        {
            $orderValue += $item->unit_price;
        }
        return round($orderValue,2);
    }

    private function getAverageUnitPrice(array $items) : float
    {
        $orderValue = $this->getTotalOrderValue($items);
        $unitCount = $this->getItemCount($items, self::TOTAL_COUNT);
        if($unitCount > 0) {
            $avgPrice = $orderValue / $unitCount;
        }
        else {
            $avgPrice = 0;
        }
        return round($avgPrice,2);
    }

    private function getItemCount(array $items, int $countMode)
    {
        if($countMode == self::TOTAL_COUNT) {
            $unitCount = 0;
            foreach($items as $item) {
                $unitCount += $item->quantity;
            }    
        }
        else {
            $unitCount = count($items);
        }

        return $unitCount;
    }

    private function exportCsv(array $data)
    {
        $encoder = new CsvEncoder();
        file_put_contents('storage/out.csv', $encoder->encode($data, 'csv', [
            'csv_delimiter' => ',',
            'csv_enclosure' => '"',
        ]));
    }

    private function sendEmail(string $emailAddress)
    {
        $email = (new Email())
        ->from('prabhu.shan@gmail.com')
        ->to($emailAddress)
        ->subject('Orders CSV file')
        ->text('Please download the attached file')
        ->attachFromPath('storage/out.csv');
        $this->mailer->send($email);
    }
}