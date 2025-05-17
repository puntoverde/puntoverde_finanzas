<?php

$router->group(["prefix"=>"tipo-accion"],function() use ($router){

      $router->get('','TipoAccionController@getTipoAccion');
      
});
