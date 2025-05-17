<?php
use Carbon\Carbon;
if(!function_exists('d_now'))
{
function d_now()
{
    return Carbon::now()->toDateTimeString();
}
}