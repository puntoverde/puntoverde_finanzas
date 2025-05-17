<?php

$router->group(["prefix"=>"prueba-timezone"],function() use ($router){

      $router->post('','PruebaTimeZoneController@insertDates');
      
});
