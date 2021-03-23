<?php
// Source: https://developer.okta.com/blog/2019/02/04/create-and-verify-jwts-in-php


namespace Phapi;

use Exception;
use Phapi;

class JWT {
  private static string $secret = 'asd';
  private static int $validity = 60 * 60 * 24;


  private static function base64UrlEncode($text) {
    return str_replace(
      ['+', '/', '='],
      ['-', '_', ''],
      base64_encode($text)
    );
  }

  public static function generate($data) {
    $header = json_encode([
      'typ' => 'JWT',
      'alg' => 'HS256',
    ]);

    // Create the token payload
    $payload = json_encode(array_merge($data, ['exp' => time() + self::$validity]));

    // Encode Header
    $base64UrlHeader = self::base64UrlEncode($header);

    // Encode Payload
    $base64UrlPayload = self::base64UrlEncode($payload);

    // Create Signature Hash
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);

    // Encode Signature to Base64Url String
    $base64UrlSignature = self::base64UrlEncode($signature);

    // Create JWT
    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    return  $jwt;
  }

  public static function validate($jwt) {
    // split the token
    $tokenParts = explode('.', $jwt);
    $header = base64_decode($tokenParts[0]);
    $payload = base64_decode($tokenParts[1]);
    $signatureProvided = $tokenParts[2];


    // build a signature based on the header and payload using the secret
    $base64UrlHeader = self::base64UrlEncode($header);
    $base64UrlPayload = self::base64UrlEncode($payload);
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);
    $base64UrlSignature = self::base64UrlEncode($signature);

    // verify it matches the signature provided in the token
    $signatureValid = ($base64UrlSignature === $signatureProvided);

    if (!$signatureValid)
      throw new Exception('JWT signature is invalid.');

    // check the expiration time - note this will cause an error if there is no 'exp' claim in the token
    $decodedToken = json_decode($payload, true);
    $expiration = $decodedToken['exp'];
    $tokenExpired = time() > $expiration;
    if ($tokenExpired)
      return null;

    return $decodedToken;
  }

  public static function TokenParserMiddleware(&$parameters, $next) {
    try {

      // Load bearer token 
      $headers = null;
      if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
      } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
      } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
          $headers = trim($requestHeaders['Authorization']);
        }
      }
      // HEADER: Get the access token from the header
      $token = null;
      if (!empty($headers)) {
        if (preg_match('/Bearer\s((.*)\.(.*)\.(.*))/', $headers, $matches)) {
          $token = $matches[1];
        }
      }

      $payload = null;
      if ($token != null)
        $payload = JWT::validate($token);


      if ($payload != null) {
        Phapi::setContext('user', Phapi::service('Auth')->getUser($payload['userid']));
      }
    } catch (Exception $e) {
    }
    $next();
  }
}
