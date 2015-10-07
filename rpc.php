<?php
require_once('vendor/autoload.php');
date_default_timezone_set("Europe/Moscow");

define('PRODUCTION', false);

error_reporting(E_ALL);
ini_set('display_errors','On');

require_once('utils/config.php');


function real_ip()
{
  global $_SERVER;
  $words = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];

  foreach ($words as $word)
    if (isset($_SERVER[$word]))
      return $_SERVER[$word];
  die();
}

function phoxy_conf()
{
  $ret = phoxy_default_conf();
  global $_SERVER;
  $ret["ip"] = real_ip();
  $ret['adminip'] = false;
  $ret['api_xss_prevent'] = PRODUCTION;
  if (!$ret['adminip'])
    ini_set('display_errors','Off');
  return $ret;
}

function default_addons( $name )
{
  $ret =
  [
    "cache" => "no",
    "result" => "canvas",
  ];
  return $ret;
}

$arr = explode('REDIRECTIT', $_SERVER['QUERY_STRING']);
if (count($arr) != 3)
  die('RPC: Invalid htaccess redirect');
$rpc_string = $arr[1];
if ($rpc_string == '/api/')
  $rpc_string = '/api/main/Home';
$_GET['api'] = $rpc_string;


include('phoxy/phoxy_return_worker.php');
phoxy_return_worker::$add_hook_cb = function($that)
{
  global $USER_SENSITIVE;

  if ($USER_SENSITIVE)
    $that->obj['cache'] = 'no';
};

include('phoxy/index.php');