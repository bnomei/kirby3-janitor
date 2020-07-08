<?php

declare(strict_types=1);

namespace Bnomei;

use Exception;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use League\CLImate\CLImate;

final class Janitor
{
    /**
     * @var array
     */
    private $options;

    /**
     * Janitor constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $defaults = [
            'debug' => option('debug'),
            'log' => option('bnomei.janitor.log.fn'),
            'jobs' => option('bnomei.janitor.jobs'),
            'jobs-defaults' => ['bnomei.janitor.jobs-defaults'],
            'jobs-extends' => option('bnomei.janitor.jobs-extends'),
            'secret' => option('bnomei.janitor.secret'),
        ];
        $this->options = array_merge($defaults, $options);

        $extends = array_merge($this->options['jobs-defaults'], $this->options['jobs-extends']);
        foreach ($extends as $extend) {
            // NOTE: it is intended that jobs override merged not other way around
            $this->options['jobs'] = array_change_key_case(
                array_merge(option($extend, []), $this->options['jobs'])
            );
        }

        foreach ($this->options as $key => $call) {
            if (is_callable($call) && in_array($key, ['secret'])) {
                $this->options[$key] = $call();
            }
        }
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function option(?string $key = null)
    {
        if ($key) {
            return A::get($this->options, $key);
        }
        return $this->options;
    }

    /**
     * @param string $secret
     * @param string $name
     * @param array $data
     * @return array
     */
    public function jobWithSecret(string $secret, string $name, array $data = []): array
    {
        if ($secret === $this->option('secret')) {
            return $this->job($name, $data);
        }
        return [
            'status' => 401,
        ];
    }

    /**
     * @param string $name
     * @param array $data
     * @return array
     */
    public function job(string $name, array $data = []): array
    {
        $job = $this->findJob($name);

        if (!is_string($job) && is_callable($job)) {
            return $this->jobFromCallable($job, $data);
        } elseif (class_exists($job)) {
            return $this->jobFromClass($job, $data);
        }

        return [
            'status' => 404,
        ];
    }

    /**
     * @return mixed
     */
    public function listJobs()
    {
        // find in jobs config
        return array_keys($this->option('jobs'));
    }

    /**
     * @param string $name
     * @return mixed|string
     */
    public function findJob(string $name)
    {
        // find in jobs config
        $jobInConfig = A::get($this->option('jobs'), strtolower($name));
        if ($jobInConfig) {
            return $jobInConfig;
        }

        // could be a class
        return $name;
    }

    /**
     * @param $job
     * @param array $data
     * @return array
     */
    public function jobFromCallable($job, array $data): array
    {
        $return = false;
        set_time_limit(0);

        try {
            $return = $job(
                page(str_replace('+', '/', urldecode(A::get($data, 'contextPage', '')))),
                str_replace('+S_L_A_S_H+', '/', urldecode(A::get($data, 'contextData', '')))
            );
        } catch (\BadMethodCallException $ex) {
            $return = $job();
        }
        if (is_array($return)) {
            return $return;
        }
        return [
            'status' => $return ? 200 : 404,
        ];
    }

    /**
     * @param string $job
     * @param array $data
     * @return array
     */
    public function jobFromClass(string $job, array $data): array
    {
        $object = new $job(
            page(str_replace('+', '/', urldecode(A::get($data, 'contextPage', '')))),
            str_replace('+S_L_A_S_H+', '/', urldecode(A::get($data, 'contextData', '')))
        );

        if (method_exists($object, 'job')) {
            set_time_limit(0);
            return $object->job();
        }
        return [
            'status' => 400,
        ];
    }

    /**
     * @param string $msg
     * @param string $level
     * @param array $context
     * @return bool
     */
    public function log(string $msg = '', string $level = 'info', array $context = []): bool
    {
        $log = $this->option('log');
        if ($log && is_callable($log)) {
            if (!$this->option('debug') && $level == 'debug') {
                // skip but...
                return true;
            } else {
                return $log($msg, $level, $context);
            }
        }
        return false;
    }

    /*
     * @var Janitor
     */
    private static $singleton;

    /**
     * @param array $options
     * @return Janitor
     */
    public static function singleton(array $options = []): Janitor
    {
        if (self::$singleton) {
            return self::$singleton;
        }

        self::$singleton = new Janitor($options);
        return self::$singleton;
    }

    /**
     * @param string|null $template
     * @param mixed|null $model
     * @return string
     */
    public static function query(string $template = null, $model = null): string
    {
        $page = null;
        $file = null;
        if ($model && $model instanceof Page) {
            $page = $model;
        } elseif ($model && $model instanceof File) {
            $file = $model;
        }
        return Str::template($template, [
            'kirby' => kirby(),
            'site' => kirby()->site(),
            'page' => $page,
            'file' => $file,
            'user' => kirby()->user(),
        ]);
    }

    /**
     * @param $val
     * @param bool $return_null
     * @return bool
     */
    public static function isTrue($val, $return_null = false): bool
    {
        $boolval = (is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val);
        $boolval = ($boolval === null && !$return_null ? false : $boolval);
        return $boolval;
    }

    /**
     * @var \League\CLImate\CLImate
     */
    private static $climate;

    /**
     * @param CLImate|null $climate
     * @return CLImate|null
     */
    public static function climate(?CLImate $climate = null): ?CLImate
    {
        if ($climate && !self::$climate) {
            self::$climate = $climate;
        }
        return self::$climate;
    }
}
