--TEST--
Fiber that is never resumed
--SKIPIF--
<?php include __DIR__ . '/include/skip-if.php';
--FILE--
<?php

require dirname(__DIR__) . '/scripts/bootstrap.php';

$loop = new Loop;

$fiber = new Fiber(function () use ($loop): void {
    try {
        echo "fiber\n";
        echo Fiber::suspend($loop);
        echo "after await\n";
    } catch (Throwable $exception) {
        echo "exit exception caught!\n";
    }

    echo "end of fiber should not be reached\n";
});

$loop->defer(fn() => $fiber->start());

$promise = new Success($loop);
$promise->schedule(Fiber::this());
Fiber::suspend($loop);

echo "done\n";

--EXPECTF--
fiber
done
