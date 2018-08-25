<?php
namespace app\demo\controller;

use app\demo\controller\Token;

class User extends Base
{

    public function test()
    {
        echo 123;
    }

    public function getToken()
    {
        $token = Token::createToken();
        $validateResult =   Token::validateToken($token);
        var_dump($validateResult);
    }
}

