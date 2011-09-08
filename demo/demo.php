<?php

namespace spriebsch\factory\demo\application;

// We define some aliases for the various classes we are going to use

use spriebsch\factory\MasterFactory;
use spriebsch\factory\FactoryException;

use spriebsch\factory\demo\application\ServiceLocator as ServiceLocator;
use spriebsch\factory\demo\framework\Factory as FrameworkFactory;
use spriebsch\factory\demo\framework\OriginalFactory;
use spriebsch\factory\demo\library\Factory as LibraryFactory;

// Load everything we need

require __DIR__ . '/../src/autoload.php';
require __DIR__ . '/Library.php';
require __DIR__ . '/Framework.php';
require __DIR__ . '/Application.php';

// We have created a factory that wraps the original framework factory
$frameworkFactory = new FrameworkFactory(new OriginalFactory());

// This is the library's factory
$libraryFactory = new LibraryFactory();

// We create the master factory and register all other factories as children
$factory = new MasterFactory();
$factory->register($libraryFactory);
$factory->register($frameworkFactory);

// Purely optional, we can create a service locator that offers an explicit
// API instead of just a getInstanceFor() method
$serviceLocator = new ServiceLocator($factory);

// The service locator can now create objects by delegating to the master
// factory, which in turn delegates to the appropriate child factory
var_dump($serviceLocator->getServiceA());
var_dump($serviceLocator->getServiceB());
var_dump($serviceLocator->getServiceC());
var_dump($serviceLocator->getServiceD());

// We can also ask the master factory directly for instances
var_dump(get_class($factory->getInstanceFor('library_A')));
var_dump(get_class($factory->getInstanceFor('library_B')));

// By printing the factory (or casting it to string), we get an overwiev
// of all registered child factories and the types they can create
print $factory;

// Re-registering a factory will not work (neither will registering a factory
// that is not capable of instantiating any types)

try {
    $factory->register($libraryFactory);
}

catch (FactoryException $e) {
    var_dump('Cannot re-register factory');
}
