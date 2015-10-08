<?php

session_start();
class test extends api
{
  protected function Reserve()
  {
    return
    [
      "design" => "test/entry",
      "data" => $_SESSION,
    ];
  }

  protected function Send($to, $message)
  {
    $text = urldecode($message);
    return $this('api/integration', 'check')->welcome($to, $text);
  }
}