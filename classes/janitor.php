<?php

namespace Bnomei;

use \Kirby\Toolkit;

class Janitor
{
    public static function cacheRemoveUnusedFiles() {
        $krc = kirby()->roots()->cache();
        $exclude = option('bnomei.janitor.exclude');
        $c = 0;
        foreach (static::cacheFolders() as $folder) {
            $cache = kirby()->cache(\str_replace(DIRECTORY_SEPARATOR, '.', $folder));
            foreach (\Kirby\Toolkit\Dir::files($krc.DIRECTORY_SEPARATOR.$folder) as $file) {
                if(!$cache->get($file)) { // expired
                    $fpath = $krc.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$file;
                    $success = true;
                    if (!option('bnomei.janitor.simulate') && \Kirby\Toolkit\F::exists($fpath)) {
                        $success = \Kirby\Toolkit\F::remove($fpath);
                    }
                    if($success) {
                        $c++;
                        static::log('#janitor removed expired cache-file [' . $folder.DIRECTORY_SEPARATOR.$file . ']', 'warning');
                    }
                }
            }
        }
        return $c > 0;
    }

    public static function cacheClearFolders() {
        $krc = kirby()->roots()->cache();

        $c = 0;
        foreach (static::cacheFolders() as $folder) {
            $f = $krc.DIRECTORY_SEPARATOR.$folder;
            if (!\is_dir($f)) {
                continue;
            }
            $success = true;
            if (!option('bnomei.janitor.simulate')) {
                $success = \Kirby\Toolkit\Dir::remove($f);
            }
            if ($success) {
                $c++;
                static::log('#janitor removed cache folder [' . $folder . ']', 'warning');
            } else {
                static::log('#janitor failed to remove cache-folder [' . $folder . ']', 'error');
            }
        }
        return $c > 0;
    }

    public static function cacheFlush() {
        $krc = kirby()->roots()->cache();
        $exclude = option('bnomei.janitor.exclude');
        $c = 0;
        foreach (static::cacheFolders() as $folder) {
            $cacheName = \str_replace(DIRECTORY_SEPARATOR, '.', $folder);
            if ($cache = kirby()->cache($cacheName)) {
                if (!option('bnomei.janitor.simulate')) {
                    $cache->flush();
                }
                $c++;
                static::log('#janitor flushed cache [' . $cacheName . ']', 'warning');
            }
        }
        return $c > 0;
    }

    public static function cacheRepair() {
        $krc = kirby()->roots()->cache();
        if (!\is_dir($krc)) {
            if(\Kirby\Toolkit\Dir::make($krc)) {
                static::log('#janitor recreated the root cache folder', 'warning');
            } else {
                static::log('#janitor failed to create the root cache folder', 'error');
            }
        } else {
            static::log('#janitor found nothing in need of reparation');
        }
        return true;
    }

    public static function lootTheSafe(): int {
        // NOTE: used to test the api $_$
        $loot = random_int(1, 9);
        \sleep(1);
        return $loot;
    }

    public static function cacheFolders(bool $all = false): array {
        $krc = kirby()->roots()->cache();
        $exclude = option('bnomei.janitor.exclude');

        $folders = [];
        foreach(\Kirby\Toolkit\Dir::index($krc, true) as $file) {
            $skip = false;
            $path = $krc . DIRECTORY_SEPARATOR . $file;
            if (\is_dir($path) && count(\Kirby\Toolkit\Dir::dirs($path)) == 0) {
                if (!$all) {
                    foreach ($exclude as $e) {
                        if (\Kirby\Toolkit\Str::contains($file, \trim($e, DIRECTORY_SEPARATOR))) {
                            $skip = true;
                            break;
                        }
                    }
                }
                if (!$skip) {
                    $folders[$file] = true;
                }
            }
        }
        return \array_keys($folders);
    }

    public static function api(string $job, bool $remote = false, string $secret = null): array {
        if(!$remote || ($secret && $secret == option('bnomei.janitor.secret'))) {
            $job = \Kirby\Toolkit\Str::slug($job);

            $defaults = option('bnomei.janitor.jobs.defaults', []);
            $jobs = \array_merge($defaults, option('bnomei.janitor.jobs', []));
            foreach(option('bnomei.janitor.jobs.extends', []) as $optionID) {
                $jobs = \array_merge($jobs, option($optionID, []));
            }
            
            $before = \time();
            $data = [];
            $success = false;

            if(\array_key_exists($job, $jobs)) {
                $c = $jobs[$job];
                if (\is_callable($c)) {
                    static::log('@' . $job . ' started');
                    $r = $c();
                    if(\is_array($r)) {
                        $data = $r;
                        $v = \Kirby\Toolkit\A::get($data, 'status', 404);
                        $success = \intval($v) == 200;
                    } else {
                        $success = \boolval($r);
                    }
                    static::log('@' . $job . ' finished');
                } else {
                    static::log('@' . $job . ' not callable', 'warning');
                }
            } else {
                static::log('@' . $job . ' not found', 'warning');
            }

            $after = \time();
            return \array_merge([
                'job' => $job,
                'started' => $before,
                'finished' => $after,
                'status' => $success ? 200 : 404,
                // 'label' => $job, // custom jobs only
            ], $data);
        }
        return [];
    }

    public static function log(string $msg = '', string $level = 'info', array $context = []):bool
    {
        $log = option('bnomei.janitor.log');
        if ($log && \is_callable($log)) {
            if (!option('debug') && $level == 'debug') {
                // skip but...
                return true;
            } else {
                return $log($msg, $level, $context);
            }
        }
        return false;
    }
}
