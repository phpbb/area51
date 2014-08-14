<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

// Use APC for autoloading to improve performance.
// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.
/*
$loader = new ApcClassLoader('sf2', $loader);
$loader->register(true);
*/

require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

if (($profilerkey = getenv('QAFOO_SYMFONY_API_KEY'))) {
	\QafooLabs\Profiler::start($profilerkey);
}

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

if (($profilerkey = getenv('QAFOO_SYMFONY_API_KEY'))) {
	\QafooLabs\Profiler::setTransactionName($request->attributes->get('_controller', 'notfound'));
}

$kernel->terminate($request, $response);
