<?php
namespace app\demo\controller;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;

class Token {

    //生成token
    public static function createToken()
    {
        $signer  = new Sha256();
        $builder = new Builder();
        $salt    =  (string) "123";
        $token   =  $builder->setIssuer("http://www.apis.gezlife.com")
                ->setAudience("http://www.apis.gezlfe.net")
                ->setId("sxs-4f1g23a12aa", true)
                ->setIssuedAt(time())
                ->setExpiration(time() + 60)
                ->set("uid", 123)
                ->sign($signer, $salt)
                ->getToken()
                ->__toString();

        return (string) $token;
    }

    //检验token 有没有过期
    public static function validateToken($token)
    {
        $tokenObj   =   (new Parser())->parse((string) $token);
        $signer     =   new Sha256();
        $salt       =   (string) "123";
        if(!$tokenObj->verify($signer, $salt)) {
            return false;
        }

        $validationData     =   new ValidationData();
        $validationData->setIssuer("http://www.apis.gezlife.com");
        $validationData->setAudience("http://www.apis.gezlfe.net");
        $validationData->setId("sxs-4f1g23a12aa");
        return  $tokenObj->validate($validationData);
    }









}
