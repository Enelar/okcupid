<?php
include("../vendor/autoload.php");

$less = new lessc;

header("Content-Type: text/css");
header('Cache-Control: public, max-age=3600');
try
{
  echo $less->compileFile('a.less');
} catch(Exception $e)
{
  echo $e->getMessage();
}