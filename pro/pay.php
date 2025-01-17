<?php
  require_once 'session.php';
  require_once '../constants.php';
  if (!isset($_SESSION['amount'], $_SESSION['email'])) {
    @session_destroy();
    header("Location: ../");
    exit;
  }



  $pay = curl_init();
  $email = $_SESSION['email'];
  $amount = $_SESSION['amount'] . "00";
  // die($amount);
  //the amount in kobo. This value is actually NGN 5000
  curl_setopt_array($pay, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_CUSTOMREQUEST => "POST",

    CURLOPT_POSTFIELDS => json_encode([
      'amount' => $amount,
      'email' => $email,
    'currency' => 'USD',
    'callback_url' => 'http://www.safetravels.com/pro/verify.php'
    ]),
    CURLOPT_HTTPHEADER => [
    "authorization: Bearer sk_test_3647ffe292919befb3c7b681cc07fb66859b3889", //replace this with your own test key
      "content-type: application/json",
      "cache-control: no-cache"
    ],
  ));

//So that curl_exec returns the contents of the cURL; rather than echoing it
curl_setopt($pay, CURLOPT_RETURNTRANSFER, true); 

  $response = curl_exec($pay);
  $err = curl_error($pay);
  if ($err) {
    header("Location: individual.php?page=pay&error=payment&access=0");
    exit();
  }
  $tranx = json_decode($response);
  if (!$tranx->status or empty($tranx->status)) {
  // there was an error from the API
  header("Location: individual.php?page=pay&error=" . $tranx->error . "&access=1");
    exit();
  }

  // redirect to page so User can pay
  // uncomment this line to allow the user redirect to the payment page
  header('Location: ' . $tranx->data->authorization_url);