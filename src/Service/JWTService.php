<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService 
{
    // on genere  le token
    /**
     * Generation du JWT
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param int $validity
     * @return string
     */

    public function generate(array $header, array $payload, string $secret, int $validity): string
    {
        if($validity >0){
            
        $now = new DateTimeImmutable();
        $exp = $now-> getTimestamp() + $validity;

        $payload['iat'] = $now->getTimestamp();
        $payload['exp'] = $exp;
        }


        // On encode en base64

        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        // on "nettoie" les valeurs encodes (retrait des +,/ et =)
        
        $base64Header = str_replace(['+','/','='],['-','_',''], $base64Header);
        $base64Payload = str_replace(['+','/','='],['-','_',''], $base64Payload);

        // on genere la signature

        $signature = hash_hmac('sha256', $base64Header.'.'. $base64Payload, $secret, true);

        $base64Signature = base64_encode($signature);

        $base64Signature =  str_replace(['+','/','='],['-','_',''], $base64Signature);

        // on cree le token

        $jwt = $base64Header. '.' . $base64Payload . '.' . $base64Signature;

        return $jwt;

    }

    // on verifie que le token est valid (correctement former)

    public function isValid(string $token): bool 
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]', $token
        ) === 1;
    }

    // on recupere le payload
    public function getPayLoad(string $token): array
    {
        // on demonte le token
        $array = explode('.',$token);

        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
;
    }

     // on recupere le Header
     public function getHeader(string $token): array
     {
         // on demonte le token
         $array = explode('.',$token);
            
         // on decode le header
         $header = json_decode(base64_decode($array[0]), true);
 
         return $header;
 ;
     }

    // on verifie si le token a expire 
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayLoad($token);

        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    // On verifie la signature du token 

    public function check(string $token, string $secret)
    {
        // on recuperer le header et le payload

        $header = $this->getHeader($token);
        $payload = $this->getPayLoad($token);

        // on recupere un token
        $verifToken = $this->generate($header,$payload, $secret, 0);

        return $token === $verifToken;

    }


}