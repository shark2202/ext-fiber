--TEST--
Nested schedulers 2
--SKIPIF--
<?php include __DIR__ . '/include/skip-if.php';
--FILE--
<?php

require dirname(__DIR__) . '/scripts/bootstrap.php';

$loop1 = new Loop;
$loop2 = new Loop;

$fiber = new Fiber(function () use ($loop1, $loop2): void {
    $promise1 = new Promise($loop1);
    $promise2 = new Promise($loop2);
    $promise3 = new Promise($loop2);
    $promise4 = new Promise($loop1);

    $loop1->delay(20, fn() => $promise1->resolve(1));
    $loop2->delay(10, fn() => $promise2->resolve(2));
    $loop2->delay(100, fn() => $promise3->resolve(3));
    $loop1->delay(10, fn() => $promise4->resolve(4));

    $fiber = Fiber::this();

    $promise1->schedule($fiber);
    echo Fiber::suspend($loop1);

    $promise2->schedule($fiber);
    echo Fiber::suspend($loop2);

    $promise3->schedule($fiber);
    echo Fiber::suspend($loop2);

    $promise4->schedule($fiber);
    echo Fiber::suspend($loop1);
});

$loop1->defer(fn() => $fiber->start());

$fiber = new Fiber(function () use ($loop1, $loop2): void {
    $promise5 = new Promise($loop1);
    $promise6 = new Promise($loop2);
    $promise7 = new Promise($loop1);

    $loop1->delay(5, fn() => $promise5->resolve(5));
    $loop2->delay(30, fn() => $promise6->resolve(6));
    $loop1->delay(5, fn() => $promise7->resolve(7));

    $fiber = Fiber::this();

    $promise5->schedule($fiber);
    echo Fiber::suspend($loop1);

    $promise6->schedule($fiber);
    echo Fiber::suspend($loop2);

    $promise7->schedule($fiber);
    echo Fiber::suspend($loop1);
});

$loop1->defer(fn() => $fiber->start());

$promise = new Success($loop1);
$promise->schedule(Fiber::this());
Fiber::suspend($loop1);

// Note that $loop2 blocks $loop1 until $promise6 is resolved, which is why the timers appear to finish out of order.

--EXPECT--
5671234
