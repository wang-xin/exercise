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
    public function bb()
    {

    }
}

$test = Container::getInstance()->get('Test2');
$test->aa();
