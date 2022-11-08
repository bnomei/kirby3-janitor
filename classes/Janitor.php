<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\CLI\CLI;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

final class Janitor
{
    /** @var $data array */
    private static $data;

    public function data(string $command, ?array $data = null): ?array
    {
        if ($data) {
            Janitor::$data[$command] = $data;
        }
        return A::get(Janitor::$data, $command);
    }

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
            'secret' => option('bnomei.janitor.secret'),
        ];
        $this->options = array_merge($defaults, $options);

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
    public function jobWithSecret(string $secret, string $name, array $args = []): array
    {
        if ($secret === $this->option('secret')) {
            return $this->job($name, $args);
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
    public function job(string $name, array $args = []): array
    {
        CLI::command($name, ...$args);

        return $this->data($name) ?? [
            'status' => 200,
            'message' => 'Janitor has no data from command "' . $name . '".',
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
        $user = kirby()->user();
        if ($model && $model instanceof Page) {
            $page = $model;
        } elseif ($model && $model instanceof File) {
            $file = $model;
        } elseif ($model && $model instanceof User) {
            $user = $model;
        }

        return Str::template($template, [
            'kirby' => kirby(),
            'site' => kirby()->site(),
            'page' => $page,
            'file' => $file,
            'user' => $user,
            'model' => $model ? get_class($model) : null,
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
}
