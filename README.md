# Kirby 3 Janitor

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-janitor?color=ae81ff)
![Stars](https://flat.badgen.net/packagist/ghs/bnomei/kirby3-janitor?color=272822)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-janitor?color=272822)
![Issues](https://flat.badgen.net/packagist/ghi/bnomei/kirby3-janitor?color=e6db74)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-janitor)](https://travis-ci.com/bnomei/kirby3-janitor)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-janitor)](https://coveralls.io/github/bnomei/kirby3-janitor) 
[![Demo](https://flat.badgen.net/badge/website/examples?color=f92672)](https://kirby3-plugins.bnomei.com/janitor) 
[![Gitter](https://flat.badgen.net/badge/gitter/chat?color=982ab3)](https://gitter.im/bnomei-kirby-3-plugins/community) 
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)


Kirby 3 Plugin for running jobs like cleaning the cache from within the Panel, PHP code or a cronjob.

> *TIP 1:* The Janitor plugin can also perform other *jobs* than the build-in _cache jobs_. 
  
> *TIP 2:* It can also create logs of what it did.

## Commerical Usage

This plugin is free but if you use it in a commercial project please consider to 
- [make a donation 🍻](https://www.paypal.me/bnomei/5) or
- [buy me ☕](https://buymeacoff.ee/bnomei) or
- [buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/35731?link=1170)

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-janitor/archive/master.zip) as folder `site/plugins/kirby3-janitor` or
- `git submodule add https://github.com/bnomei/kirby3-janitor.git site/plugins/kirby3-janitor` or
- `composer require bnomei/kirby3-janitor`

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
    data: Grand # (string) forwarded to job context
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
            'heist' => function(Kirby\Cms\Page $page = null, string $data = null) {
                \Bnomei\Janitor::log('heist.mask '.time());
                
                $grand = \Bnomei\Janitor::lootTheSafe();
                // or trigger a snippet like this:
                // snippet('call-police');
                
                // $page is Kirby Page Object if job issued by Panel
                $location = $page ? $page->title() : 'Bank';
                
                // $data is optional [data] prop from the Janitor Panel Field
                $currency = $data ? $data : 'Coins';
                
                \Bnomei\Janitor::log('heist.exit '.time());
                return [
                    'status' => $grand > 0 ? 200 : 404,
                    'label' => $grand . ' ' . $currency . '  looted at ' . $location . '!'
                ];
            }
        ],
        // ... other configs
    ];
```

## Predefined Jobs

- **tend** removes cache-files *that have expired* from all but excluded cache-folders.
- **clean** removes cache-files from all but excluded cache-folders.
- **flush** calls `flush()` on *all* cache-folders. Dangerous!
- **repair** creates the root cache folder if it is missing.


## Panel context page and data

Since 1.3.0 you can access the Page-Object the Panel-Field was called at and the forwarded custom data prop.


```yaml
  myjob:
    type: janitor
    label: Perform Job
    progress: Performing Job...
    job: myjob
    data: my custom data
```

```php
'myjob' => function(Kirby\Cms\Page $page = null, string $data = null) {
    // $data == 'my custom data'
}
```

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

