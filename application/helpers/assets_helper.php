<?php
/**
 * Created by PhpStorm.
 * User: jhpassion0621
 * Date: 3/4/16
 * Time: 9:14 AM
 */

defined('BASEPATH') OR exit('No direct script access allowed');

function asset_base_url(){
    $CI = & get_instance();
    if (!$dir = $CI->config->item('assets_path')) {
        $dir = 'assets/';
    }
    return $CI->config->base_url($dir);
}