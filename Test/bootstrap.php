<?php

include __DIR__ . "/../vendor/autoload.php";

// backward compatibility (https://stackoverflow.com/a/42828632/187780)
if (!class_exists('\PHPUnit\Framework\TestCase') && class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}
