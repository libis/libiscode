<?php

//User end point url
  $userEndpoint = 'http://forum.emis.vito.local/user_endpoint/user/';

  
  //Login
  $data = array(
    'username' => 'Rest service gebruiker',
    'password' => 'tsertser',
  );
  $headers = array(
    'content-type' => 'application/x-www-form-urlencoded',
  );
  
  $r = new HttpRequest($userEndpoint . 'login', HttpRequest::METH_POST);
  $r->setHeaders($headers);
  $r->setPostFields($data);
    
  try {
    $login = $r->send();
    $data = json_decode($r->data);
    echo 'login ok ' . ($login->getHeader('Set-Cookie')) . ' <br/>';
  } catch (HttpException $ex) {
    echo $ex;
    return;  
}







$topicEndpoint = 'http://forum.emis.vito.local/topic_endpoint/node/';

  //Create topic
  $data = array(
    'title'	          => 'title: test new!!!',
    'type'            => 'forum',
    'body'	          => array('und' => array('0' => array('value' => 'testesttest', 'format' => 'full_html'))),
  	'taxonomy_forums' => array(
      'und' => 1,
    ),
    // Set these two fields to change forum topic author
    'uid' => '15',
    'name' => 'updated user'
  );
  $headers = array(
    'content-type' => 'application/x-www-form-urlencoded',
'Cookie' => $login->getHeader('Set-Cookie')
  );

    
  if($login->getResponseCode() == 200){
    $r = new HttpRequest($topicEndpoint, HttpRequest::METH_POST);
    $r->setHeaders($headers);
    $r->setPostFields($data);
    
    try {
      $response = $r->send()->getBody();
      echo $response;
    } catch (HttpException $ex) {
      echo $ex;
    }
  }







  //Logout  
  $r = new HttpRequest($userEndpoint . 'logout', HttpRequest::METH_POST);
    
$headers = array('Cookie' => $login->getHeader('Set-Cookie'));
$r->setHeaders($headers);
  try {
    $login = $r->send()->getBody();
    echo 'logout ok<br/>';
  } catch (HttpException $ex) {
    echo $ex;
  }



