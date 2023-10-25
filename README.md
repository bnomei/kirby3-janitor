# Kirby 3 Janitor

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-janitor?color=ae81ff)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-janitor?color=272822)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-janitor)](https://travis-ci.com/bnomei/kirby3-janitor)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-janitor)](https://coveralls.io/github/bnomei/kirby3-janitor)
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-janitor)](https://codeclimate.com/github/bnomei/kirby3-janitor)
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

Kirby 3 Plugin for running commands.

- It is a Panel Button!
- It has commands built-in for cleaning the cache, sessions, create zip-backup, pre-generate thumbs, open URLs, refresh the current Panel page and more.
- You can define your own commands (call API hooks, play a game, hack a server, ...)
- It can be triggered in your frontend code, with the official kirby CLI and a CRON.

## Install

Using composer:

```bash
composer require getkirby/cli bnomei/kirby3-janitor
```

You need to install the CLI with composer. Since Janitor depends on the CLI to be available installing only the janitor plugin via submodules or via ZIP do not make much sense. Please stick to composer for now.

## Commercial Usage

> <br>
> <b>Support open source!</b><br><br>
> This plugin is free but if you use it in a commercial project please consider to sponsor me or make a donation.<br>
> If my work helped you to make some cash it seems fair to me that I might get a little reward as well, right?<br><br>
> Be kind. Share a little. Thanks.<br><br>
> &dash; Bruno<br>
> &nbsp;

| M | O | N | E | Y |
|---|----|---|---|---|
| [Github sponsor](https://github.com/sponsors/bnomei) | [Patreon](https://patreon.com/bnomei) | [Buy Me a Coffee](https://buymeacoff.ee/bnomei) | [Paypal dontation](https://www.paypal.me/bnomei/15) | [Hire me](mailto:b@bnomei.com?subject=Kirby) |

## Setup

### CLI Command

In any blueprint create a janitor field with your command, browse to that page in panel and press the button.

**site/blueprints/page/default.yml**
```yml
title: Default Page
fields:
  call_my_command:
    type: janitor
    command: 'example --data test'
    label: Call `Example` Command
```

Janitor will automatically fill in the current model.

- The `--model` argument will have the UUID or ID of the current model. You can use `janitor()->model($cli->arg('model))` to get the object.
- But if for example you press the panel button on a page you will have `--page` argument set to the UUID or ID of that page. Use `$cli->kirby()->page($cli->arg('page'))` to get the object.
- If you call it on a file view then `--file` arg will be set. Use `$cli->kirby()->file($cli->arg('file'))` to get the object.
- On a panel user view... `--user`. Use `$cli->kirby()->user($cli->arg('user'))` to get the object.
- And lastly `--site` (boolean) will be automatically set when you had the button in the `site/blueprints/site.yml` blueprint. `if($cli->arg('site')) { $cli->kirby()->site(); }`

Create a Kirby CLI command [via a custom plugin](https://getkirby.com/docs/reference/plugins/extensions/commands) or put them into `site/commands`.

**site/commands/example.php**
```php
<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;

return [
    'description' => 'Example',
    'args' => [] + Janitor::ARGS, // page, file, user, site, data, model
    'command' => static function (CLI $cli): void {
        $page = page($cli->arg('page'));

        // output for the command line
        $cli->success(
            $page->title() . ' ' . $cli->arg('data')
        );

        // output for janitor
        janitor()->data($cli->arg('command'), [
            'status' => 200,
            'message' => $page->title() . ' ' . $cli->arg('data'),
        ]);
    }
];

```

### Callback

Instead of using a command you can also create a callback in a custom plugin options or any config file.

**site/config/config.php**
```php
<?php

return [
    'example' => function ($model, $data = null) {
        return [
            'status' => 200,
            'message' => $model->title() . ' ' . $data,
        ];
    },
    // ... other options
];
```

The Janitor plugin has a special command `janitor:job` that you can use to trigger your callback.

**site/blueprints/page/default.yml**
```yml
title: Default Page
fields:
  call_my_command:
    type: janitor
    command: 'janitor:job --key example --data test'
    label: Call `Example` Command
```

The `$model` will match the model of whatever page, file, user or site object you pressed the button at.

> Why just a single model variable instead of one each for page, file, user and site? For one reason to make it work directly with any existing version 2 callbacks you might have already created and secondly because this why it is very easy to get the model that triggered the callback.

### Built in commands and examples

This plugin comes with a [few commands](https://github.com/bnomei/kirby3-janitor/tree/master/commands) you might like to use yourself and some [example commands](https://github.com/bnomei/kirby3-janitor/tree/master/tests/site/commands) used to showcase the various options the button has (like how to change the icon or open a URL in a new tab). Some commands can be used in both panel and terminal. Others are limited in their use to either one of them. In the terminal you can use `--help` argument to view the help for each command.

- `janitor:backupzip`, creates a backup zip
- `janitor:call`, calls a method on the current model with optional data parameter
- `janitor:cleancontent`, removes fields from content file that are not defined in your blueprints
- `janitor:clipboard`, copies a defined value to your clipboard
- `janitor:download`, triggers a download of an URL
- `janitor:flush`, flush a cache by providing its name
- `janitor:job`, run a callback
- `janitor:maintenance`, toggle maintenance mode
- `janitor:open`, triggers opening of an URL in panel
- `janitor:pipe`, map input argument to output argument
- `janitor:render`, render a certain page or all pages (to create thumb jobs)
- `janitor:thumbs`, process thumb jobs of a certain page or all pages
- `janitor:tinker`, run a REPL session in terminal
- `janitor:undertaker`, backups a page and its subpages to a zip. You need to manually trigger it with a [hook](https://github.com/bnomei/kirby3-janitor/blob/master/tests/site/config/config.php).

The plugin will register these commands starting with `janitor:*` automatically - no copying required.<br>But if you want to re-use any of the other example provided you need to copy them to your `site/commands`-folder

### Blueprint field options

The button you create with the `field: janitor` in your blueprint can be configured to do various things. Checkout the [example default.yml blueprint](https://github.com/bnomei/kirby3-janitor/blob/master/tests/site/blueprints/pages/default.yml) to familiarize yourself with how to use it.

- `autosave`, if `true` then save before pressing the button
- `backgroundColor`, sets backgroundColor of button
- `color`, sets text color of button
- `confirm`, sets text for confirmation after clicking the button and before executing the command, can prevent the execution of the command if the user clicks `cancel` in the OS dialog
- `command`, command like you would enter it in terminal, with [query language support](https://getkirby.com/docs/guide/blueprints/query-language) and page/file/user/site/data arguments
- `cooldown`, time in milliseconds the message is flashed on the button (default: 2000)
- `error`, set message to show on all **non-200**-status returns with query language support
- `help`, set help of the button
- `icon`, set the [icon](https://getkirby.com/docs/reference/panel/icons) of the button
- `intab`, if `true` then use in combination with the `open`-option to open an URL in a new tab
- `label`, set label of the button
- `progress`, set message to show while the button waits for the response, with query language support
- `success`, set message to show on all **200**-status returns, with query language support
- `unsaved`, if `false` then disable the button if panel view has unsaved content

### Janitor API options

In either the command or the callback you will be setting/returning data to the Janitor button via its api. Depending on what you return you can trigger various things to happen in the panel.

- `backgroundColor`, see `backgroundColor`-field option
- `clipboard`, string to copy to clipboard
- `color`, see `color`-field option
- `download`, URL to start downloading
- `error`, see `error`-field option
- `help`, see `help`-field option
- `icon`, see `icon`-field option
- `label`, see `label`-field option
- `message`, see `message`-field option
- `open`, URL to open, use with `intab`-field option to open in a new tab
- `reload`, if `true` will reload panel view once api call is received
- `success`, see `success`-field option
- `status`, return `200` for a **green** button flash, anything else for a **red** flash

### Examples

Again... check out the [built-in commands](https://github.com/bnomei/kirby3-janitor/tree/master/commands) and plugin [example commands](https://github.com/bnomei/kirby3-janitor/tree/master/tests/site/commands) to learn how to use the field and api options yourself.

```yml
test_ping:
  type: janitor
  command: 'ping' # see tests/site/commands/ping.php
  label: Ping
  progress: ....
  success: Pong
  error: BAMM

janitor_open:
  type: janitor
  command: 'janitor:open --data {{ user.panel.url }}'
  intab: true
  label: Open current user URL in new tab
  icon: open
  # the open command will forward the `data` arg to `open` and open that URL

janitor_clipboarddata:
  type: janitor
  command: 'janitor:clipboard --data {{ page.title }}'
  label: 'Copy "{{ page.title }}" to Clipboard'
  progress: Copied!
  icon: copy
  # the clipboard command will forward the `data` arg to `clipboard` and copy that

janitor_download:
  type: janitor
  command: 'janitor:download --data {{ site.index.files.first.url }}'
  label: Download File Example
  icon: download
  # the download command will forward the `data` arg to `download` and start downloading that

janitor_backupzip:
  type: janitor
  command: 'janitor:backupzip'
  cooldown: 5000
  label: Generate Backup ZIP
  icon: archive

janitor_render:
  type: janitor
  command: 'janitor:render'
  label: Render pages to create missing thumb jobs

janitor_thumbssite:
  type: janitor
  command: 'janitor:thumbs --site'
  label: Generate thumbs from existing thumb jobs (full site)

janitor_callWithData:
  label: Call method on model with Data
  type: janitor
  command: 'janitor:call --method repeatAfterMe --data {{ user.id }}'
```

If you want you can also call any of [the core shipping with the CLI](https://github.com/getkirby/cli#available-core-commands) like `clear:cache`.

Keep in mind that the Janitor panel button and webhooks will append the `--quiet` option on all commands automatically to silence outputs to the non-existing CLI. But if you use `janitor()->command()` you will have to append `--quiet` to your command yourself.

### Running commands in your code

You can run any command in you own code as well like in a model, template, controller or hook. Since commands do not return data directly you need to retrieve data stored for Janitor using a helper `janitor()->data($commandName)`.

#### Get data returned from a command
```php
Kirby\CLI\CLI::command('whistle'); // tests/site/commands/whistle.php
var_dump(janitor()->data('whistle'));
```

#### Create and download a backup

**site/config/config.php**
```php
<?php

return [
    // ATTENTION: choose a different secret!
    'bnomei.janitor.secret' => 'e9fe51f94eadabf54',

    'routes' => [
        // custom webhook endpoint reusing janitors secret
        [
            'pattern' => 'webhook/(:any)/(:any)',
            'action' => function($secret, $command) {
                if ($secret != janitor()->option('secret')) {
                    \Kirby\Http\Header::status(401);
                    die();
                }

                if ($command === 'backup') {
                    janitor()->command('janitor:backupzip --quiet');
                    $backup = janitor()->data('janitor:backupzip')['path'];
                    if (F::exists($backup)) {
                        \Kirby\Http\Header::download([
                            'mime' => F::mime($backup),
                            'name' => F::filename($backup),
                        ]);
                        readfile($backup);
                        die(); // needed to make content type work
                    }
                }
            }
        ],
    ],
];
```

#### Calling a command with parameters

Supplying parameter to the core CLI functions can be a bit tricky since you need to separate argument key and argument values. It seems easy with one but gets a bit tedious with a dynamic list of parameters and if values contain `space`-chars or quotes. But fret not – Janitor has a helper for that as well.

```php
Kirby\CLI\CLI::command('uuid', '--page', 'some/page'); // tests/site/commands/uuid.php

janitor()->command('uuid --page some/page');

var_dump(janitor()->data('uuid')['message']); // page://82h2nkal12ls
```

> Remember that using the `janitor()->command($string)`-helper you can call any of your own commands and the core commands as well, not just the ones defined by Janitor.

If you want to work with command strings yourself you can use the following static helper method.

```php
list($name, $args) = Bnomei\Janitor::parseCommand('uuid --page page://82h2nkal12ls');
Kirby\CLI\CLI::command($name, ...$args);
```


### Webhook with secret

You can not call Janitors api unauthenticated. You either need to use the panel button or you can set a `secret` in your `site/config/config.php` file and call the janitor api URL with that secret.

**site/config/config.php**
```php
<?php

return [
  'bnomei.janitor.secret' => 'e9fe51f94eadabf54', // whatever string you like
  //... other options
];
```

You could also use a callback if you want to store the secret in a `.env` file and have it loaded by my [dotenv plugin](https://github.com/bnomei/kirby3-dotenv).

**/.env**
```dotenv
# whatever key and value you like
MY_JANITOR_SECRET=e9fe51f94eadabf54
```

**site/config/config.php**
```php
<?php

return [
  'bnomei.janitor.secret' => fn() => env('MY_JANITOR_SECRET'),
  //... other options
];
```

#### example URL
```
https://dev.bnomei.com/plugin-janitor/e9fe51f94eadabf54/janitor%3Abackupzip
```

#### example URL with urlencoded arguments
```
http://dev.bnomei.com/plugin-janitor/e9fe51f94eadabf54/janitor%3Athumbs%20--site
```
### CRON

#### Webhook with wget or curl

You can also use the secret to trigger a job using wget or curl.

```php
wget https://dev.bnomei.com/plugin-janitor/e9fe51f94eadabf54/janitor%3Abackupzip --delete-after
// or
curl -s https://dev.bnomei.com/plugin-janitor/e9fe51f94eadabf54/janitor%3Abackupzip > /dev/null
```

Are you having issues with PHP bin and cron? [read this](https://github.com/bnomei/kirby3-janitor/issues/105).

#### Kirby CLI (installed with composer)

in your cron scheduler add the following command

```
cd /path/to/my/kirby/project/root && vendor/bin/kirby janitor:backupzip
```

## Maintenance Mode

You can toggle maintenance mode with a janitor button like this:

```yml
  janitor_maintenance:
    type: janitor
    command: 'janitor:maintenance --user {{ user.uuid }}'
    cooldown: 5000
    label: 'Maintenance: {{ site.isUnderMaintenance.ecco("DOWN","UP") }}'
    icon: '{{ site.isUnderMaintenance.ecco("cancel","circle") }}'
```

If you need to add a custom check when maintenance mode is enforced you can do this by providing a callback for the `bnomei.janitor.maintenance.check` option.

**site/config/config.php**
```php
<?php

return [
    // return `true` for maintenance and `false` to skip maintenance
    'bnomei.janitor.maintenance.check' => function(): bool {
        // example: block unless it is a logged-in user and it has the admin role
        return kirby()->users()->current()?->role()->isAdmin() !== true;
    },
    // other options...
];
```

You can also overwrite the maintenance snippet if you create your own and store it as `site/snippets/maintenance.php`.

## Dependencies

- [Kirby CLI](https://github.com/getkirby/cli)
- [CLImate](https://github.com/thephpleague/climate)
- [Symfony Finder](https://symfony.com/doc/current/components/finder.html)

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-janitor/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.

