<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Bnomei\Janitor;

test('singleton', function () {
    // create
    $janitor = Janitor::singleton();
    expect($janitor)->toBeInstanceOf(Janitor::class);

    // from static cached
    $janitor = Janitor::singleton();
    expect($janitor)->toBeInstanceOf(Janitor::class);
});
test('option', function () {
    $janitor = new Janitor([
        'debug' => true,
        'secret' => function () {
            return 'secret';
        },
    ]);

    expect($janitor->option('debug'))->toBeTrue();
    expect($janitor->option('secret') === 'secret')->toBeTrue();

    expect($janitor->option())->toBeArray();
});
test('construct', function () {
    $janitor = new Janitor();
    expect($janitor)->toBeInstanceOf(Janitor::class);
});
test('job', function () {
    $janitor = new Janitor();
    expect($janitor->command('janitor:job  --key some.key.to.task --site')['status'])->toEqual(200);

    expect($janitor->command('janitor:job  --key some.key.to.task --site --data "some data"')['message'])->toEqual('site:// some data');
});
test('method', function () {
    $janitor = new Janitor();
    expect($janitor->command('janitor:call --method whoAmI --page page://vf0xqIlpU0ZlSorI')['status'])->toEqual(200);

    expect($janitor->command('janitor:call --method repeatAfterMe --data hello --page page://vf0xqIlpU0ZlSorI')['message'])->toEqual('Repeat after me: hello');

    expect($janitor->command('janitor:call --method nullberry --page page://vf0xqIlpU0ZlSorI')['status'])->toEqual(200);

    expect($janitor->command('janitor:call --method boolberry --page page://vf0xqIlpU0ZlSorI')['status'])->toEqual(204);
});
