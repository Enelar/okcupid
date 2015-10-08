<?php

session_start();

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
      if (is_array($post))
        $post = http_build_query($post);

      //var_dump($post);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    //curl_setopt($ch, CURLOPT_VERBOSE, 1);
    //curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_ENCODING ,"utf-8");

    $cookiefile = tempnam('/tmp', 'foo');

    file_put_contents($cookiefile, $_SESSION['cookie']);
    curl_setopt ($ch, CURLOPT_COOKIEFILE, $cookiefile);
    curl_setopt ($ch, CURLOPT_COOKIEJAR, $cookiefile);

    $server_output = curl_exec ($ch);

    //echo(curl_getinfo($ch, CURLINFO_HEADER_OUT));

    curl_close ($ch);

    $_SESSION['cookie'] = file_get_contents($cookiefile);

    return $server_output;
  }

  private function request_no_json($url, $post = null, $headers = [])
  {
    $basic_headers =
    [
      "accept" => "application/json, text/javascript, */*; q=0.01",
      "accept-language" => "en-US,en;q=0.8",
      "cache-control" => "no-cache",
      "pragma" => "no-cache",
      "referer" => "https://www.okcupid.com/",
      "user-agent" => "Mozilla/5.0 (X11; Fedora; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2522.1 Safari/537.36",
      "x-requested-with" => "XMLHttpRequest",
      "x-okcupid-platform" => "DESKTOP",
      "content-type" => "application/x-www-form-urlencoded; charset=UTF-8",
    ];

    foreach ($headers as $k => $v)
      $basic_headers[$k] = $v;

    return $this->curl($url, $post, $basic_headers);
  }

  private function request($url, $post = null, $headers = [])
  {
    $text = $this->request_no_json($url, $post, $headers);
    $obj = json_decode($text, true);

    phoxy_protected_assert($obj, ["error" => "Failure at json decode", "json" => $text]);
    return new \phpa2o\phpa2o($obj);
  }

  protected function country()
  {
    //conf()->strings->contry;
  }

  protected function city($city)
  {
    $city = urlencode($city);
    $url = "https://www.okcupid.com/apitun/location/query?&access_token=&q={$city}";
    $res = $this->request($url);

    $res->check = $res->status == 0;
    return
    [
      'data' => $res,
    ];

    // {"results" : [{"postal_code" : "", "nameid" : 3806601, "display_state" : 0, "locid" : 270999, "state_code" : "48", "country_name" : "Russia", "longitude" : 3761556, "popularity" : 5019, "state_name" : "48", "country_code" : "RS", "city_name" : "Moscow", "metro_area" : 0, "latitude" : 5575222}], "status" : 0}
  }

  protected function email($email)
  {
    $url = "https://www.okcupid.com/signup?check_email={$email}";
    $res = $this->request($url);

    $res->check = $res->valid && $res->available;
    return
    [
      'data' => $res,
    ];

    // {"email" : "test@", "valid" : true, "available" : true}
  }

  protected function nickname($username)
  {
    $url = "https://www.okcupid.com/signup";
    $post =
    [
      'check_screenname' => $username,
      'num' => '5',
      'template' => 'signup',
    ];

    $headers =
    [
      'referer' => 'https://www.okcupid.com/login?p=/onboarding/steps',
    ];

    $res = $this->request($url, $post, $headers);

    $res->check = $res->valid && $res->available;
    return
    [
      'data' => $res,
    ];

    // {"valid" : true, "available" : true}
  }

  protected function password($password)
  {
    $url = "https://www.okcupid.com/1/apitun/okc/check_password";
    $post =
    [
      'password' => $password,
    ];

    $res = $this->request($url, $post);

    $res->check = $res->is_valid;
    return
    [
      'data' => $res,
    ];

    // {"is_valid" : false}
  }

  protected function signup($obj)
  {
    $url = "https://www.okcupid.com/signup";

    $post =
    [
      "experiment_name" => "2014_simpleblue",
      "corrections_page" => "/signup/paths/2014_simpleblue/index.html",
      "start_time" => time() - 1000,
      "status" => 1,
      "opento" => 1,
      "opento" => 2,
      "opento" => 3,
      "success_page" => "/signup/tracker.html",
      "cf" => "loggedout_new_template",
      "orientation" => $obj->orientation,
      "orientation_dropdown" => $obj->orientation,
      "gender" => $obj->gender,
      "gender_dropdown" => $obj->gender,
      "birthmonth" => $obj->birthmonth,
      "birthday" => $obj->birthday,
      "birthyear" => $obj->birthyear,
      "country_select" => $obj->country,
      "zip_or_city" => $obj->city,
      "locid" => "270999",
      "lquery" => $obj->city,
      "email" => $obj->email,
      "email2" => $obj->email,
      "screenname" => $obj->nickname,
      "password" => "qwertyqwerty",
      "gender_tags" => "999",
      "orientation_tags" => "999",
    ];

    $res = $this->request_no_json($url, $post);

    if (strpos($res, "Prove that you’re human"))
      return
      [
        "issue" => "Sorry, service ask CAPCHA for this IP. Currently prototype cant solve it",
      ];

    $token = $this->login($obj->nickname, "qwertyqwerty");

    //var_dump($_SESSION, $token);

    if (!$token)
      return
      [
        "issue" => "Registration failed. Empty server answer. Is your IP blacklisted already?",
      ];

    $_SESSION['login'] = $obj->nickname;
    $_SESSION['password'] = "qwertyqwerty";

    return
    [
      "reset" => "/test",
    ];
  }

  protected function login($username, $password)
  {
    $url = "https://www.okcupid.com/login";

    $_SESSION['login'] = $username;
    $_SESSION['password'] = $password;

    $post =
    [
      "username" => $username,
      "password" => $password,
      "okc_api" => 1,
    ];

    //var_dump($post);

    $headers =
    [
      "origin" => "https://www.okcupid.com",
      "referer" => "https://www.okcupid.com/",
      "x-requested-with" => "XMLHttpRequest",
    ];

    $res = $this->request_no_json($url, $post, $headers);

    return $_SESSION['token'] = $this->get_access_token();
  }

  public function get_access_token()
  {
    $site = $this->curl("https://www.okcupid.com/home");

    // var ACCESS_TOKEN = "1,0,1444325985,0xfcc4725ec8412007;26e3fe8859b9493631ecc12f80c7c7a674768fb9";
    $match = preg_match("/var ACCESS_TOKEN = \"(.*?)\"/", $site, $matches);

    phoxy_protected_assert($match, "Login failed!! Maybe your IP hit black list?");

    phoxy_protected_assert(count($matches) > 1, "Access token not found! Api changed!");

    return $matches[1];
  }

  public function send_to($name, $message)
  {
    phoxy_protected_assert($_SESSION['token'], "Login required!!");
    $url = "http://www.okcupid.com/apitun/messages/send?&access_token={$_SESSION['token']}";

    $post =
    [
      "body" => $message,
      "is_mutual_match" => 0,
      "only_messaging_group" => "",
      "receiverid" => "17122215878593439789",
      "reply" => "1",
      "source" => "desktop_messages",
      "threadid" => "1250354456975657002",
    ];

    // var_dump($post);

    $headers =
    [
      "origin" => "https://www.okcupid.com",
      "referer" => "https://www.okcupid.com/",
      "x-requested-with" => "XMLHttpRequest",
    ];

    $res = $this->request_no_json($url, json_encode($post), $headers);
  }

  protected function welcome($name, $message)
  {
    phoxy_protected_assert($_SESSION['token'], "Login required!!");
    $url = "http://www.okcupid.com/apitun/messages/send?&access_token={$_SESSION['token']}";

    $post =
    [
      "body" => $message,
      "only_messaging_group" => "",
      "panel_group" => "control",
      "profile_tab" => "profile",
      "receiverid" => $this->get_receiver_by_nick($name),
      "reply" => 0,
      "service" => "profile",
      "source" => "desktop_global",
    ];

    $res = $this->request($url, json_encode($post));
    return $res->success && $res->status == 0 && $res->pending == 0;
  }

  protected function get_receiver_by_nick($nick)
  {
    $site = $this->curl("http://www.okcupid.com/profile/{$nick}");

    //echo $site;
    // GlobalMessaging.open('4205836261586580239')
    $match = preg_match("/GlobalMessaging.open.*?(\d+)/", $site, $matches);
    phoxy_protected_assert($match, "Receiver id determination failed. Api changed?");
    phoxy_protected_assert(count($matches) > 1, "Access token not found! Api changed!");

    return $matches[1];
  }
}