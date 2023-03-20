<?php

declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

use Bnomei\Janitor;
use PHPUnit\Framework\TestCase;

final class JanitorTest extends TestCase
{
	public function testSingleton()
	{
		// create
		$janitor = Janitor::singleton();
		$this->assertInstanceOf(Janitor::class, $janitor);

		// from static cached
		$janitor = Janitor::singleton();
		$this->assertInstanceOf(Janitor::class, $janitor);
	}

	public function testOption()
	{
		$janitor = new Janitor([
			'debug' => true,
			'secret' => function () {
				return 'secret';
			},
		]);

		$this->assertTrue($janitor->option('debug'));
		$this->assertTrue($janitor->option('secret') === 'secret');

		$this->assertIsArray($janitor->option());
	}

	public function test__construct()
	{
		$janitor = new Janitor();
		$this->assertInstanceOf(Janitor::class, $janitor);
	}

	public function testJob()
	{
		$janitor = new Janitor();
		$this->assertEquals(
			200,
			$janitor->command('janitor:job  --key some.key.to.task --site')['status']
		);

		$this->assertEquals(
			'site:// some data',
			$janitor->command('janitor:job  --key some.key.to.task --site --data "some data"')['message']
		);
	}

	public function testMethod()
	{
		$janitor = new Janitor();
		$this->assertEquals(
			200,
			$janitor->command('janitor:call --method whoAmI --page page://vf0xqIlpU0ZlSorI')['status']
		);

		$this->assertEquals(
			'Repeat after me: hello',
			$janitor->command('janitor:call --method repeatAfterMe --data hello --page page://vf0xqIlpU0ZlSorI')['message']
		);
	}
}
