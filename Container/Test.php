<?php

include './Container.php';

class Test1
{
    public function aa()
    {
        echo 'aa';
    }
}

class Test2 extends Test1
{
    public $xx;

    public function __construct($xx = 'abc')
    {
        $this->xx = $xx;
    }

    public function bb()
    {
        echo $this->xx;
    }
}

$test = Container::getInstance()->make('Test2', ['xx' => 'abcde']);
$test->bb();
