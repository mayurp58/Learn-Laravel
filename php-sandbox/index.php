<?php
require __DIR__ .'/vendor/autoload.php';

use Wcities\PhpSandbox\Greeter;

$g = new Greeter();
echo $g->hello("Mayur");