<?php
declare(strict_types=1);
namespace App\Tests;

use App\Command\FetchOrderCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FetchOrderCommandTest extends KernelTestCase
{
    public function testCommand()
    {
        self::bootKernel();
        $container = static::getContainer();

        $fetchOrderCommand = $container->get(FetchOrderCommand::class);
        $this->assertInstanceOf(FetchOrderCommand::class, $fetchOrderCommand);
        return $fetchOrderCommand;
    }

    /**
     * @depends testCommand
     * @param FetchOrderCommand $fetchOrderCommand
     */
    public function testGetDataFromUrl(FetchOrderCommand $fetchOrderCommand)
    {
        $response = $this->callMethod($fetchOrderCommand, 'getDataFromUrl');
        $this->assertTrue($response);
        return $response;
    }

    /**
     * @depends testCommand
     * @depends testGetDataFromUrl
     * @param FetchOrderCommand $fetchOrderCommand
     * @param bool $response
     */
    public function testProcessFile(FetchOrderCommand $fetchOrderCommand, bool $response)
    {
        if($response) {
            $data = $this->callMethod($fetchOrderCommand, 'processFile');
            $this->assertIsArray($data);
            $this->assertNotEmpty($data);
            var_dump($data);
        }
    }

    private function callMethod($object, string $method, array $parameters = [])
    {
        try {
            $className = get_class($object);
            $reflection = new \ReflectionClass($className);
        }
        catch (\ReflectionException $e) {
           throw new \Exception($e->getMessage());
        }

        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}