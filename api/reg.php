<?php

class reg extends api
{
  protected function Reserve()
  {
    return
    [
      "design" => "reg/entry",
    ];
  }

  protected function check($name, $value)
  {
    return strlen($value) > 5;
  }

  protected function country_list()
  {
    return conf()->strings->country;
  }

  protected function submit($obj)
  {
    return $this('api/integration', 'check', true)->signup($obj);
  }
}