<?php

$f3=require(__DIR__.'/lib/base.php'); // path to f3

// Set up
$test=new Test;
include('hello.php');

// This is where the tests begin
$test->expect(
    is_callable('hello'),
    'hello() is a function'
);

// Another test
$hello=hello();
$test->expect(
    !empty($hello),
    'Something was returned'
);

// This test should succeed
$test->expect(
    is_string($hello),
    'Return value is a string'
);

// This test is bound to fail
$test->expect(
    strlen($hello)==13,
    'String length is 13'
);

// Display the results; not MVC but let's keep it simple
foreach ($test->results() as $result) {
    echo $result['text'].'<br>';
    if ($result['status'])
        echo 'Pass';
    else
        echo 'Fail ('.$result['source'].')';
    echo '<br>';
}