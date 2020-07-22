<?php 
// Source: https://developer.okta.com/blog/2019/02/04/create-and-verify-jwts-in-php

class JWT {
  private static string $secret = 'asd';
  private static int $validity = 60 * 60 * 24;


  private static function base64UrlEncode($text)
  {
      return str_replace(
          ['+', '/', '='],
          ['-', '_', ''],
          base64_encode($text)
      );
  }

  public static function generate($data){
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

  public static function validate($jwt){
    // split the token
    $tokenParts = explode('.', $jwt);
    $header = base64_decode($tokenParts[0]);
    $payload = base64_decode($tokenParts[1]);
    $signatureProvided = $tokenParts[2];
    $decodedToken = json_decode($payload, true);

    // check the expiration time - note this will cause an error if there is no 'exp' claim in the token
    $expiration = $decodedToken['exp'];
    $tokenExpired = time() < $expiration;

    // build a signature based on the header and payload using the secret
    $base64UrlHeader = self::base64UrlEncode($header);
    $base64UrlPayload = self::base64UrlEncode($payload);
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);
    $base64UrlSignature = self::base64UrlEncode($signature);

    // verify it matches the signature provided in the token
    $signatureValid = ($base64UrlSignature === $signatureProvided);

    if ($tokenExpired) 
      throw new Exception('JWT token has expired.');

    if ($signatureValid) 
      throw new Exception('JWT signature is invalid.');

    return $decodedToken;
  }
}