<?php

class config
{
  private $config = false;

  public function __invoke()
  {
    if (!$this->config)
      $this->config = $this->init();
    return $this->config;
  }

  private function init()
  {
    $public =
    [
      'secret_location' => './secret.yaml',
      'config_location' => './config.yaml',
    ];

    $config = $public;
    $config = array_merge_recursive($config, $this->load_file($public['config_location']));
    $config = array_merge_recursive($config, $this->load_file($public['secret_location']));

    $config = new \phpa2o\phpa2o($config);

    return $config;
  }

  private function load_file($file)
  {
    if (!file_exists($file))
      die("Failute at yaml config parse. '$file' doesnt exsist");
    if (function_exists('yaml_parse_file'))
      return yaml_parse_file($file);
    if (function_exists('spyc_load_file'))
      return spyc_load_file($file);
    die('Failure at yaml config parse. No tool available to reach this goal');
  }
}

$config = new config();
function conf()
{
  global $config;
  return $config();
}
