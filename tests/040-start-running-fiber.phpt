--TEST--
Start running fiber
--SKIPIF--
<?php include __DIR__ . '/include/skip-if.php';
--FILE--
<?php

require dirname(__DIR__) . '/scripts/bootstrap.php';

$loop = new Loop;

$fiber = new Fiber(function () use ($loop): void {
    $fiber = Fiber::this();
    $loop->delay(10, fn() => $fiber->resume());
    Fiber::suspend($loop);
});

$loop->defer(fn() => $fiber->start());
$loop->defer(fn() => $fiber->start());

$fiber = Fiber::this();
$loop->defer(fn() => $fiber->resume());
Fiber::suspend($loop);

--EXPECTF--
Fatal error: Uncaught FiberError: Cannot start a fiber that has already been started in %s:%d
Stack trace:
#0 %s(%d): Fiber->start()
#1 %s(%d): {closure}()
#2 %s(%d): Loop->tick()
#3 [fiber function](0): Loop->run()
#4 {main}

Next FiberExit: Uncaught FiberError thrown from Loop::run(): Cannot start a fiber that has already been started in %s:%d
Stack trace:
#0 %s(%d): Fiber::suspend(Object(Loop))
#1 {main}
  thrown in %s on line %d
