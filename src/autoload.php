<?php // @startCodeCoverageIgnore
spl_autoload_register(
   function($class) {
      static $classes = null;
      if ($classes === null) {
         $classes = array(
            'spriebsch\\factory\\abstractfactory' => '/AbstractFactory.php',
            'spriebsch\\factory\\childfactoryinterface' => '/interfaces/ChildFactoryInterface.php',
            'spriebsch\\factory\\factoryexception' => '/exceptions/FactoryException.php',
            'spriebsch\\factory\\factoryinterface' => '/interfaces/FactoryInterface.php',
            'spriebsch\\factory\\masterfactory' => '/MasterFactory.php',
            'spriebsch\\factory\\masterfactoryinterface' => '/interfaces/MasterFactoryInterface.php'
          );
      }
      $cn = strtolower($class);
      if (isset($classes[$cn])) {
         require __DIR__ . $classes[$cn];
      }
   }
);
