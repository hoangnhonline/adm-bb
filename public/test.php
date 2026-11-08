<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,'https://drive.google.com/drive/folders/1Oqxxh6p46TBAdRsinFvwTtDxOmIfm7oc?usp=sharing');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
   
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36");
   
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'GET');
    curl_setopt ($ch, CURLOPT_HEADER, 0);
    $exec=curl_exec ($ch);
var_dump($exec);die;
//Dữ liệu thời tiết ở dạng JSON
$weather = json_decode($resp);
var_dump($weather);

curl_close($curl);