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
 */

 namespace Radion\Console\Test;

use PHPUnit\Framework\TestCase;
use Radion\Component\Console\Application;
use Radion\Component\Console\Command;

class CommandTest extends TestCase
 {
    protected static $fixturesPath;

    public function testConstructor()
    {
        $command = new Command();
        $application = new Application();
        $application->setCommand('foo:bar');
        $this->assertEquals('foo:bar', $command->getSignature(), '__construct() takes the command name as its first argument');
    }

    public function testSetApplication()
    {
        $application = new Application();
        $command = new Command();
        $command->defineApp($application);
        $this->assertEquals($application, $command->getApp() , '->defineApp() sets the current application');
        $this->assertEquals($application->showHelp($command), $command->showHelp($command));
    }

    public function testAddOption()
    {
        $command = new Application();
        $ret = $command->setOption('foo');
        $this->assertEquals($command, $ret, '->setOption() implements a fluent interface');
        $this->assertTrue($command->hasOption('foo'), '->setOption() adds an option to the command');
    }

    public function testAddArgument()
    {
        $command = new Application();
        $ret = $command->setArgument('foo');
        $this->assertEquals($command, $ret, '->setArgument() implements a fluent interface');
        $this->assertTrue($command->hasArgument('foo'), '->setArgument() adds an argument to the command');
    }
 }