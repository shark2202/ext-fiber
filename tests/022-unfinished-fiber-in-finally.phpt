--TEST--
Fiber that is never resumed with finally block
--SKIPIF--
<?php
include __DIR__ . '/include/skip-if.php';
--FILE--
<?php

require dirname(__DIR__) . '/scripts/bootstrap.php';

$loop = new Loop;

$loop->defer(function () use ($loop): void {
    $fiber = new Fiber(function () use ($loop): object {
        try {
            try {
                echo "fiber\n";
                return new \stdClass;
            } finally {
                echo "inner finally\n";
                Fiber::suspend($loop);
                echo "after await\n";
            }
        } catch (Throwable $exception) {
            echo "exit exception caught!\n";
        } finally {
            echo "outer finally\n";
        }

        echo "end of fiber should not be reached\n";
    });

    $fiber->start();
});

$promise = new Success($loop);
$promise->schedule(Fiber::this());
Fiber::suspend($loop);

echo "done\n";

--EXPECT--
fiber
inner finally
outer finally
done
