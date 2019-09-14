<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Bnomei\Janitor;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

final class JanitorTest extends TestCase
{
    public function testLog()
    {
        $janitor = new Janitor();
        $this->assertFalse($janitor->log());
    }

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
            'jobs.extends' => ['another.plugin.jobs'],
            'secret' => function () {
                return 'secret';
            },
        ]);

        $this->assertTrue($janitor->option('debug'));
        $this->assertArrayHasKey('whistle', $janitor->option('jobs'));
        $this->assertTrue($janitor->option('secret') === 'secret');

        $this->assertIsArray($janitor->option());
    }

    public function test__construct()
    {
        $janitor = new Janitor();
        $this->assertInstanceOf(Janitor::class, $janitor);
    }

    public function testFindJob()
    {
        $janitor = new Janitor([
            'jobs.extends' => ['another.plugin.jobs'],
        ]);

        $this->assertTrue(class_exists($janitor->findJob('clean')));
        $this->assertTrue(class_exists($janitor->findJob('whistle')));
        $this->assertTrue(is_callable($janitor->findJob('heist')));

        $this->assertFalse(class_exists($janitor->findJob('invalidjob')));
        $this->assertFalse(is_callable($janitor->findJob('nocall')));
    }

    public function testJobWithSecret()
    {
        $janitor = new Janitor();
        $this->assertEquals(
            401,
            $janitor->jobWithSecret('secret', 'flush')['status']
        );

        $janitor = new Janitor([
            'secret' => 'secret',
        ]);
        $this->assertEquals(
            200,
            $janitor->jobWithSecret('secret', 'flush')['status']
        );
    }

    public function testJob()
    {
        $janitor = new Janitor();
        $this->assertEquals(
            200,
            $janitor->job('flush')['status']
        );

        $this->assertEquals(
            404,
            $janitor->job('invalidjob')['status']
        );
    }

    public function testJobFromCallable()
    {
        $janitor = new Janitor();
        $this->assertRegExp(
            '/^(\d){1} Coins looted at Bank/',
            $janitor->job('heist')['label']
        );

        $this->assertEquals(
            200,
            $janitor->job('minimal')['status']
        );
    }

    public function testJobFromClass()
    {
        $janitor = new Janitor([
            'jobs.extends' => ['another.plugin.jobs'],
        ]);
        $this->assertEquals(
            '♫',
            $janitor->job('whistle')['label']
        );

        // class exists but has no method job()
        $this->expectExceptionMessageRegExp('/Argument 1 passed/');
        $this->assertIsArray(
            $janitor->job('Kirby\Cms\Page')['label']
        );
    }

    public function testBuildInJobs()
    {
        $file = kirby()->roots()->cache() . '/bnomei/test.cache';
        F::write($file, '');
        $this->assertTrue(F::exists($file));

        $janitor = new Janitor();
        // one single file to remove
        $this->assertEquals(
            200,
            $janitor->job('clean')['status']
        );
        // none to remove
        $this->assertEquals(
            204,
            $janitor->job('clean')['status']
        );

        $this->assertFalse(F::exists($file));
    }

    public function testQuery()
    {
        $this->assertEquals('', Janitor::query());
        $this->assertEquals(
            'Home',
            Janitor::query('{{ site.homepage.title }}')
        );

        $this->assertEquals(
            'Home Home',
            Janitor::query('{{ page.title }} {{ site.homepage.title }}', page('home'))
        );
    }
}
