<?php

$router->group(["prefix"=>"domicilios"],function() use ($router){

      $router->get('','DomicilioController@getDomicilioByCP');

      $router->get('/nacionalidades','DomicilioController@getNacionalidad');

      
      
});
