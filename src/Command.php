<?php

/**
 * The Biurad Library Autoload via cli
 * -----------------------------------------------.
 *
 * This is an extensible library used to load classes
 * from namespaces and files just like composer.
 * But this is built in procedural php.
 *
 * @see ReadMe.md to know more about how to load your
 * classes via command line.
 *
 * @author Divine Niiquaye <hello@biuhub.net>
 * @author Muhammad Syifa <emsifa@gmail.com>
 */

namespace Radion\Toolbox\ConsoleLite;

abstract class Command
{
    protected $app;

    protected $signature;

    protected $description;

    public function getSignature()
    {
        return $this->signature;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function defineApp(Application $app)
    {
        if (!$this->app) {
            $this->app = $app;
        }
    }

    public function getApp()
    {
        $this->app;
    }

    public function __call($method, $args)
    {
        if ($this->app and method_exists($this->app, $method)) {
            return call_user_func_array([$this->app, $method], $args);
        } else {
            $class = get_class($this);

            throw new \BadMethodCallException("Call to undefined method {$class}::{$method}");
        }
    }
}
