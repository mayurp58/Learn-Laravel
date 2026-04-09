<?php
namespace Wcities\PhpSandbox;

Class Greeter
{
    public function hello(string $name) : string
    {
        return "Hello! {$name},";
    }
}