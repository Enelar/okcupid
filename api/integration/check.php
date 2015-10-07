<?php

class check extends api
{
  private function curl($url, $post = null, $headers = [])
  {
    $head = [];
    foreach ($headers as $key => $value)
      if ($key != 'Content-Length')
        $head[] = "{$key}: {$value}";

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    if (!is_null($post))
    {
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_ENCODING ,"utf-8");

    //var_dump($post, $head);

    $server_output = curl_exec ($ch);

    curl_close ($ch);

    return $server_output;
  }

  private function request($url, $post = null, $headers = [])
  {
    $text = $this->curl($url, $post, $headers);
    $obj = json_decode($text, true);

    phoxy_protected_assert($obj, ["error" => "Failure at json decode", "json" => $text]);
    return $obj;
  }

  protected function country()
  {
    //conf()->strings->contry;
  }

  protected function city($city, $country)
  {
    $url = "https://www.okcupid.com/apitun/location/query?&access_token=&q={$city}, {$country}";
    $res = $this->curl($url);

    $res->check = $res->status != 0;
    return $res;

    // {"results" : [{"postal_code" : "", "nameid" : 3806601, "display_state" : 0, "locid" : 270999, "state_code" : "48", "country_name" : "Russia", "longitude" : 3761556, "popularity" : 5019, "state_name" : "48", "country_code" : "RS", "city_name" : "Moscow", "metro_area" : 0, "latitude" : 5575222}], "status" : 0}
  }

  protected function email($email)
  {
    $url = "https://www.okcupid.com/signup?check_email={$email}";
    $res = $this->curl($url);

    $res->check = $res->valid && $res->available;
    return $res;

    // {"email" : "test@", "valid" : true, "available" : true}
  }

  protected function username($username)
  {
    $url = "https://www.okcupid.com/signup";
    $post =
    [
      'check_screenname' => $username,
      'num' => '5',
      'template' => 'signup',
    ];

    $res = $this->curl($url, $post);

    $res->check = $res->valid && $res->available;
    return $res;

    // {"valid" : true, "available" : true}
  }

  protected function password($password)
  {
    $url = "https://www.okcupid.com/1/apitun/okc/check_password";
    $post =
    [
      'password' => $password,
    ];

    $res = $this->curl($url, $post);

    $res->check = $res->is_valid;
    return $res;

    // {"is_valid" : false}
  }

  protected function signup()
  {
    // https://www.okcupid.com/signup
    // POST

    // experiment_name:2014_simpleblue
    // corrections_page:/signup/paths/2014_simpleblue/index.html
    // start_time:1444211690968
    // status:1
    // opento:1
    // opento:2
    // opento:3
    // success_page:/signup/tracker.html
    // cf:loggedout_new_template
    // orientation:1
    // orientation_dropdown:1
    // gender:2
    // gender_dropdown:2
    // birthmonth:3
    // birthday:20
    // birthyear:1980
    // country_select:Russia
    // zip_or_city:Moscow
    // locid:270999
    // lquery:Moscow
    // email:test@
    // email2:test@
    // screenname:username674019
    // password:qwertyqwerty
    // gender_tags:999
    // orientation_tags:999

    // EMPTY
  }
}