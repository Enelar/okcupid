<?php

class main extends api
{
  protected function Reserve()
  {
    unset($this->addons['result']);

    return
    [
      'design' => 'main/body',
    ];
  }

  protected function Home()
  {
    return
    [
      'design' => 'main/home',
    ];
  }

  protected function Img()
  {
    return
    [
      'design' => 'main/img',
      'script' => 'img',
    ];
  }
}