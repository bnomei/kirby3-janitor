# Kirby 3 Janitor

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-janitor?color=ae81ff)
![Stars](https://flat.badgen.net/packagist/ghs/bnomei/kirby3-janitor?color=272822)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-janitor?color=272822)
![Issues](https://flat.badgen.net/packagist/ghi/bnomei/kirby3-janitor?color=e6db74)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-janitor)](https://travis-ci.com/bnomei/kirby3-janitor)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-janitor)](https://coveralls.io/github/bnomei/kirby3-janitor) 
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-janitor)](https://codeclimate.com/github/bnomei/kirby3-janitor) 
[![Demo](https://flat.badgen.net/badge/website/examples?color=f92672)](https://kirby3-plugins.bnomei.com/janitor) 
[![Gitter](https://flat.badgen.net/badge/gitter/chat?color=982ab3)](https://gitter.im/bnomei-kirby-3-plugins/community) 
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)


Kirby 3 Plugin for running jobs like cleaning the cache from within the Panel, PHP code or a cronjob.

> *TIP 1:* The Janitor plugin can also perform other *jobs* than the build-in _cache jobs_. 
  
> *TIP 2:* It can also create logs of what it did.

1. [Custom Jobs](https://github.com/bnomei/kirby3-janitor#custom-jobs)
1. [Query Language](https://github.com/bnomei/kirby3-janitor#queries)
1. [Reload Panel](https://github.com/bnomei/kirby3-janitor#reload-panel-view)
1. [Copy to Clipboard](https://github.com/bnomei/kirby3-janitor#copy-to-clipboard)
1. [Open URL](https://github.com/bnomei/kirby3-janitor#open-url)
1. [Download File](https://github.com/bnomei/kirby3-janitor#download-file)

## Commerical Usage

This plugin is free but if you use it in a commercial project please consider to 
- [make a donation 🍻](https://www.paypal.me/bnomei/5) or
- [buy me ☕](https://buymeacoff.ee/bnomei) or
- [buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/35731?link=1170)

## Similar plugins

- [Kirby Terminal](https://github.com/lukaskleinschmidt/kirby-terminal) if you need to execute longer running processes on the terminal
- [Kirby Queue](https://github.com/bvdputte/kirby-queue) if you want to add scheduled jobs to queque which get trigged by a cron job

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-janitor/archive/master.zip) as folder `site/plugins/kirby3-janitor` or
- `git submodule add https://github.com/bnomei/kirby3-janitor.git site/plugins/kirby3-janitor` or
- `composer require bnomei/kirby3-janitor`

## Screenshot

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

## Jobs

### Predefined Jobs

- **flush** calls `flush()` on kirbys core pages cache.
- **clean** removes all cache-files from custom caches or plugins.

### Custom Jobs

Go build your own jobs. Trigger APIs, create ZIPs, rename Files, ... check out the [examples created for the unittests](https://github.com/bnomei/kirby3-janitor/blob/master/tests/site/config/config.php).

### Extend with existing Jobs

Example: `'bnomei.janitor.jobs.extends' => ['bvdputte.kirbyqueue.queues']` will load all classnames or callbacks defined in `option('bvdputte.kirbyqueue.queues')` as well.

### Queries

The `data` properties will be parsed for queries. You can use that to for example forward the current panel users email to your custom job.

```yaml
janitor_query:
  type: janitor
  label: Query '{{ user.email }}'
  job: query
  data: '{{ user.email }}'
```

```php
'query' => function (Kirby\Cms\Page $page = null, string $data = null) {
    return [
        'status' => 200,
        'label' => $data, // this is the email
    ];
},
```



## Panel Features

### Context page and data

```yaml
  myjob:
    type: janitor
    label: Perform Job
    progress: Performing Job...
    job: myjob
    data: my custom data
```

```php
'page' => function(Kirby\Cms\Page $page = null, string $data = null) {
    // $page => page object where the button as pressed
    // $data => 'my custom data'
    return [
        'status' => 200,
        'label' => $page->title() . ' ' . $data,
    ];
}
```

### Reload Panel View

```php
'reload' => function(Kirby\Cms\Page $page = null, string $data = null) {
    return [
        'status' => 200,
        'reload' => true, // will trigger JS location.reload in panel
    ];
}
```

### Copy to Clipboard

```php
'clipboard' => function(Kirby\Cms\Page $page = null, string $data = null) {
    return [
        'status' => 200,
        'clipboard' => 'Janitor',
    ];
}
```

### Open URL

```php
'openurl' => function(Kirby\Cms\Page $page = null, string $data = null) {
    return [
        'status' => 200,
        'href' => 'https://github.com/bnomei/kirby3-janitor',
    ];
}
```

### Download File

```php
'download' => function(Kirby\Cms\Page $page = null, string $data = null) {
    return [
        'status' => 200,
        'download' => 'https://raw.githubusercontent.com/bnomei/kirby3-janitor/master/kirby3-janitor-screenshot-1.gif',
    ];
}
```

> ATTENTION: The download dialog will only appear at [same origin](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/a#Attributes) otherwise it will behave like opening an url.

## Settings

| bnomei.janitor.           | Default        | Description               |            
|---------------------------|----------------|---------------------------|
| jobs | `array of classnames or callbacks` | array of `['key' => function() { return []; } ]` |
| jobs.extends | `[]` | array of names to other job definitions. example: `['bvdputte.kirbyqueue.queues']` |
| label.cooldown | `2000` | in millisecondss. the field allow you to override this as well. | 
| secret | `null` | any string or callback |
| log.enabled | `false` | if enabled it try to call [Kirby-log Plugin](https://github.com/bvdputte/kirby-log) |

## Usage in JS, Vue and as a Cronjob

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

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-janitor/issues/new).

## Credits

- [@robinscholz](https://github.com/robinscholz) helped me a great deal with the panel field code. Thanks again! And again for v2!
- Kirby 2 https://github.com/bnomei/kirby-opener

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.

