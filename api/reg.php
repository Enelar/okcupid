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
}