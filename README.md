This connector allow you register, login, and sending messages.
It worked back in Oct 2015, i have no idea if it works now.

I used it only for fair reasons, so there no capcha workaround.
(They using girls-bots, i am using brute-force against it)

# Register
1. Get city code (`city`)
2. Get country code.. Well, look at DevTools what code in your case
3. Check if email available (`email`)
4. Check if nickname available (`nickname`)
5. Check if password meets requirements (`password`)

```
$obj =
[
  "orientation" =>
  "orientation_dropdown" => ,
  "gender" => ,
  "gender_dropdown" => ,
  "birthmonth" => ,
  "birthday" => ,
  "birthyear" => ,
  "country_select" => ,
  "zip_or_city" => ,
  "locid" => ,
  "lquery" => ,
  "email" => ,
  "email2" => ,
  "screenname" => ,
  "password" => ,
];

signup($obj);
```

# Sending

1. `login`
2. Send initial message: `welcome(nickname, message)`
2. Reply: `send_to(nickname, message)`
