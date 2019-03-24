<?php

require __DIR__.'/request.php';
use CrossKnowledgeTest\SimpleJsonRequest;
use CrossKnowledgeTest\Redis;

function doTest($title, $request)
{
    echo '-----------------------------------------------------------------------'.PHP_EOL;
    echo 'Testing Request '.$title.PHP_EOL;
    echo '-----------------------------------------------------------------------'.PHP_EOL;
    $result = $request();
    var_dump($result);
    echo '-----------------------------------------------------------------------'.PHP_EOL.PHP_EOL;
}

function tSleep()
{
    $s = rand(1, 6);
    echo ' >>> Sleeping for '.$s.' seconds <<< '.PHP_EOL.PHP_EOL;
    sleep($s);
}

$testGet = function () {
    return SimpleJsonRequest::get('https://httpbin.org/get', ['boo' => rand(0, 1)]);
};

$testPost = function () {
    return SimpleJsonRequest::post('https://httpbin.org/post', ['cache-it' => 1], ['foo' => rand(0, 1)]);
};

for ($i = 0; $i < 5; ++$i) {
    doTest('GET n'.($i + 1), $testGet);
    tSleep();
}

for ($i = 0; $i < 5; ++$i) {
    doTest('POST n'.($i + 1), $testPost);
    tSleep();
}

echo '-----------------------------------------------------------------------'.PHP_EOL;
echo 'Dumping Redis'.PHP_EOL;
echo '-----------------------------------------------------------------------'.PHP_EOL;
$r = new Redis();
var_dump($r->dump());
echo '-----------------------------------------------------------------------'.PHP_EOL.PHP_EOL;
