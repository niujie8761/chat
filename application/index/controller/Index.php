<?php
namespace app\index\controller;

use MongoDB\Driver\Manager;
use think\Controller;
use think\log;
use think\Request;

class Index extends Controller
{
    public function index()
    {
        $fromId = Request::instance()->param('fromId');
        $toId   = Request::instance()->param('toId');
        $this->assign('fromId', $fromId);
        $this->assign('toId', $toId);
        return $this->fetch();
    }

    public function lists()
    {
        $fromId = Request::instance()->param('fromId');
        $this->assign('fromId', $fromId);
        return $this->fetch();
    }

    public function test()
    {
        $data = $this->getContent('http://api.iiot.htdata.com/999/tid/4846/screen/cz/mes/114019');
        echo '<pre>';
        print_r($data);
    }

    private function getContent($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        $header = ['apiKey: dF80ODQ2X2R3YnlOT0ttamR6bEtCa21z']; //设置一个你的浏览器agent的header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //curl_setopt($ch, CURLOPT_HEADER, 1); //返回response头部信息
        curl_setopt($ch, CURLINFO_HEADER_OUT, true); //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header

        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

}

