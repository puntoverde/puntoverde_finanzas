<?php 

foreach(glob(__DIR__."/Helpers/*.php") as $filename)
{
    include $filename;
}