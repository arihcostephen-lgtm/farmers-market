<?php
require_once __DIR__ . '/../config.php';

function payment_config($name, $default = '') { return env_value($name, $default); }

function uganda_phone($phone) {
  $phone = preg_replace('/[^0-9+]/', '', (string) $phone);
  if (strpos($phone, '+256') === 0) return $phone;
  if (strpos($phone, '256') === 0) return '+' . $phone;
  if (strpos($phone, '0') === 0) return '+256' . substr($phone, 1);
  return '';
}

function payment_http($url, $headers, $body = null, $method = 'POST') {
  $handle = curl_init($url);
  curl_setopt_array($handle, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers, CURLOPT_SSL_VERIFYPEER => true]);
  if ($body !== null) curl_setopt($handle, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
  $response = curl_exec($handle);
  $result = ['status' => (int) curl_getinfo($handle, CURLINFO_HTTP_CODE), 'body' => json_decode($response ?: '', true), 'raw' => $response, 'error' => curl_error($handle)];
  curl_close($handle);
  return $result;
}

function mtn_token() {
  $auth = base64_encode(payment_config('MTN_UGANDA_API_USER') . ':' . payment_config('MTN_UGANDA_API_KEY'));
  return payment_http(payment_config('MTN_UGANDA_BASE_URL', 'https://sandbox.momodeveloper.mtn.com') . '/collection/token/', ['Authorization: Basic ' . $auth, 'Ocp-Apim-Subscription-Key: ' . payment_config('MTN_UGANDA_SUBSCRIPTION_KEY'), 'Content-Type: application/x-www-form-urlencoded']);
}

function start_mtn_payment($amount, $phone, $reference) {
  $tokenResponse = mtn_token();
  $token = $tokenResponse['body']['access_token'] ?? '';
  if ($token === '') return $tokenResponse;
  return payment_http(payment_config('MTN_UGANDA_BASE_URL', 'https://sandbox.momodeveloper.mtn.com') . '/collection/v1_0/requesttopay', ['Authorization: Bearer ' . $token, 'Ocp-Apim-Subscription-Key: ' . payment_config('MTN_UGANDA_SUBSCRIPTION_KEY'), 'X-Reference-Id: ' . $reference, 'X-Target-Environment: ' . payment_config('MTN_UGANDA_TARGET_ENVIRONMENT', 'sandbox'), 'X-Callback-Url: ' . payment_config('PAYMENT_CALLBACK_URL'), 'Content-Type: application/json'], ['amount' => number_format($amount, 2, '.', ''), 'currency' => 'UGX', 'externalId' => $reference, 'payer' => ['partyIdType' => 'MSISDN', 'partyId' => ltrim($phone, '+')], 'payerMessage' => 'Farmers Market order', 'payeeNote' => 'Farmers Market order']);
}

function start_airtel_payment($amount, $phone, $reference) {
  $baseUrl = payment_config('AIRTEL_UGANDA_BASE_URL', 'https://openapiuat.airtel.africa');
  $tokenResponse = payment_http($baseUrl . '/auth/oauth2/token', ['Content-Type: application/json'], ['client_id' => payment_config('AIRTEL_UGANDA_CLIENT_ID'), 'client_secret' => payment_config('AIRTEL_UGANDA_CLIENT_SECRET'), 'grant_type' => 'client_credentials']);
  $token = $tokenResponse['body']['access_token'] ?? '';
  if ($token === '') return $tokenResponse;
  return payment_http($baseUrl . '/merchant/v1/payments/', ['Authorization: Bearer ' . $token, 'Content-Type: application/json', 'X-Country: UG', 'X-Currency: UGX'], ['reference' => $reference, 'subscriber' => ['country' => 'UG', 'currency' => 'UGX', 'msisdn' => ltrim($phone, '+')], 'transaction' => ['amount' => $amount, 'country' => 'UG', 'currency' => 'UGX', 'id' => $reference], 'amount' => $amount, 'currency' => 'UGX', 'callback_url' => payment_config('PAYMENT_CALLBACK_URL')]);
}

function send_payment_sms($phone, $message) {
  $apiKey = payment_config('SMS_API_KEY');
  $username = payment_config('SMS_USERNAME');
  if ($apiKey === '' || $username === '') return false;
  $response = payment_http(payment_config('SMS_API_URL', 'https://api.africastalking.com/version1/messaging'), ['apiKey: ' . $apiKey, 'Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'], http_build_query(['username' => $username, 'to' => $phone, 'message' => $message]));
  return $response['status'] >= 200 && $response['status'] < 300;
}
