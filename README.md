# Kirby 3 Janitor

![GitHub release](https://img.shields.io/github/release/bnomei/kirby3-janitor.svg?maxAge=1800) ![License](https://img.shields.io/github/license/mashape/apistatus.svg) ![Kirby Version](https://img.shields.io/badge/Kirby-3%2B-black.svg)

Kirby 3 Plugin for running jobs like cleaning the cache from within the Panel, PHP code or a cronjob.

> *TIP 1:* The Janitor plugin can also perform other *jobs* than the build-in _cache jobs_. 
> *TIP 2:* It can also create logs of what it did.

## Commerical Usage

This plugin is free but if you use it in a commercial project please consider to 
- [make a donation 🍻](https://www.paypal.me/bnomei/5) or
- [buy me ☕](https://buymeacoff.ee/bnomei) or
- [buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/35731?link=1170)

## Screenshots

![clean & loot](https://raw.githubusercontent.com/bnomei/kirby3-janitor/master/kirby3-janitor-screenshot-1.gif)


## Usage Examples

**PHP code**
```php
$success = janitor('clean'); // boolean
// or
$json = janitor('clean', true); // array
```

**Panel Field will create a clickable button**
```yaml
  janitor:
    type: janitor
    label: Clean Cache
    progress: Cleaning Cache...
    job: clean
  heist:
    type: janitor
    label: Enter Bank
    progress: Performing Heist...
    job: heist
```

**Kirby API (post Authentification)**
```js
let janitor = fetch('https://devkit.bnomei.com/api/plugin-janitor/clean')
  .then(response => response.json())
  .then(json => {
      console.log(json);
  });
```

**Kirby Vue**
```vue
this.$api.get('plugin-janitor/clean')
  .then(response => {
    console.log(response)
  })
```

**Cronjob**
Set a `secret` in your config.php file and call the janitor api with secret in a crobjob like this. This way you do not need the Kirby API to authentificate.
```php
wget https://devkit.bnomei.com/plugin-janitor/clean/e9fe51f94eadabf54dbf2fbbd57188b9abee436e --delete-after
// or
curl -s https://devkit.bnomei.com/plugin-janitor/clean/e9fe51f94eadabf54dbf2fbbd57188b9abee436e > /dev/null
```

## Custom Jobs

Go build your own jobs. Trigger APIs, create ZIPs, rename Files, ...

**site/config.php**
```php
<?php
    return [
        'bnomei.janitor.jobs' => [
            'heist' => function() {
                \Bnomei\Janitor::log('heist.mask '.time());
                $grand = \Bnomei\Janitor::lootTheSafe();
                // or trigger a snippet like this:
                // snippet('call-police');
                \Bnomei\Janitor::log('heist.exit '.time());
                return [
                    'status' => $grand > 0 ? 200 : 404,
                    'label' => $grand . ' Grand looted!'
                ];
            }
        ],
        // ... other configs
    ];
```

## Predefined Jobs

- **tend** removes cache-files that have expired from all but not in excluded folders.
- **clean** removes all but excluded cache-folders.
- **flush** calls `flush()` on all but excluded folders. Dangerous.
- **repair** creates the cache folder if its missing.

## Settings

All settings must be prefixed with `bnomei.janitor.`.

**jobs**
- default: `[]` array of keys => callbacks:bool|array

**jobs.extends**
- default: `[]` array of names to other keys => callbacks:bool|array options
- example: `['bvdputte.kirbyqueue.queues']`

**label.cooldown**
- default: `2000`ms. the field allow you to override this as well. 

**exclude**
- default: `['bnomei/autoid', 'bnomei/fingerprint']` array of foldernames to exclude. 

**secret**
- default: `null`, any string

**log.enabled**
- default: `false`, if enabled it try to call [Kirby-log Plugin](https://github.com/bvdputte/kirby-log)

**simulate**
- default: `false`, will not remove any files if enabled. You could check this option in your customs jobs as well if you want to support simulating the job.

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-janitor/issues/new).

## Credits

- [@robinscholz](https://github.com/robinscholz) helped me a great deal with the panel field code. Thanks again!
- Kirby 2 https://github.com/bnomei/kirby-opener. Maybe the janitor plugin will be able to do all this again someday.

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.

