<?php // @startCodeCoverageIgnore
spl_autoload_register(
   function($class) {
      static $classes = null;
      if ($classes === null) {
         $classes = array(
            'spriebsch\\factory\\tests\\abstractfactorytest' => '/AbstractFactoryTest.php',
            'spriebsch\\factory\\tests\\masterfactorytest' => '/MasterFactoryTest.php',
            'spriebsch\\factory\\tests\\stubs\\factorystub' => '/stubs/FactoryStub.php',
            'spriebsch\\factory\\tests\\stubs\\objectstub' => '/stubs/ObjectStub.php'
          );
      }
      $cn = strtolower($class);
      if (isset($classes[$cn])) {
         require __DIR__ . $classes[$cn];
      }
   }
);
