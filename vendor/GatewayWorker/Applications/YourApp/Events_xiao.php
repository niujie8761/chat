<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use \GatewayWorker\Lib\Aes;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
      //连接成功，要求绑定
        Gateway::sendToClient($client_id,json_encode(['type'=>90000]));

    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id,$data)
   {
      //接收信息转换数组
       $NewData = json_decode($data,true);
       //好友聊天
       if($NewData['ChatType'] == 1){
          //判断是什么类型的信息
           switch($NewData['type']){
               case 90001:
                //绑定id
                   Gateway::bindUid($client_id,$NewData['id']);
                   $date = ['code'=>'ok'];
                    Gateway::sendToUid($NewData['id'], json_encode($date));
                   return;
               case 80001:
                    //文本信息
                   $text = nl2br(htmlspecialchars($NewData['data']));
                   if(mb_strlen($text) > 250){
                       $date=['type'=>'error','msg'=>'50001'];
                       Gateway::sendToUid($NewData['id'], json_encode($date));
                       return ;
                   }
                   $m_ToUserID = $NewData['m_ToUserID'];
                   $date=['type'=>80001, 'data'=>$text, 'm_FromUserID'=>$NewData['id'], 'time'=>time()];
                   //发送信息
                   Gateway::sendToUid($m_ToUserID, json_encode($date));
                   //提示列表有信息的信息
                    Gateway::sendToAll(json_encode(['type'=>'list','time'=>time()]));
                   return;
               case 80002:
                  //图片信息
                   $text = nl2br(htmlspecialchars($NewData['data']));
                   if(mb_strlen($text) > 250){
                       $date=['type'=>'error','msg'=>'50002'];
                       Gateway::sendToUid($NewData['id'], json_encode($date));
                       return ;
                   }
                   $m_ToUserID = $NewData['m_ToUserID'];
                   $date=['type'=>80002, 'data'=>$text, 'm_FromUserID'=>$NewData['id'], 'time'=>time()];
                    //发送信息
                   Gateway::sendToUid($m_ToUserID, json_encode($date));
                   //提示列表有信息的信息
                   Gateway::sendToAll(json_encode(['type'=>'list','time'=>time()]));
                   return;
               case 80003:
                  //语音信息
                   $text = nl2br(htmlspecialchars($NewData['data']));
                   if(mb_strlen($text) > 250){
                       $date=['type'=>'error','msg'=>'50003'];
                       Gateway::sendToUid($NewData['id'], json_encode($date));
                       return ;
                   }
                   $m_ToUserID = $NewData['m_ToUserID'];
                   $date=['type'=>80003, 'data'=>$text, 'm_FromUserID'=>$NewData['id'], 'time'=>time()];
                   //发送信息
                   Gateway::sendToUid($m_ToUserID, json_encode($date));
                        //提示列表有信息的信息
                   Gateway::sendToAll(json_encode(['type'=>'list','time'=>time()]));
                   return;
               case 80004:
                  //视频信息
                   $text = nl2br(htmlspecialchars($NewData['data']));
                   if(mb_strlen($text) > 250){
                       $date=['type'=>'error','msg'=>'50003'];
                       Gateway::sendToUid($NewData['id'], json_encode($date));
                       return ;
                   }
                   $m_ToUserID = $NewData['m_ToUserID'];
                   $date=['type'=>80004, 'data'=>$text, 'm_FromUserID'=>$NewData['id'], 'time'=>time()];
                   Gateway::sendToUid($m_ToUserID, json_encode($date));
                   Gateway::sendToAll(json_encode(['type'=>'list','time'=>time()]));
                   return;

           }
           //群聊天
       }elseif($NewData['ChatType'] == 2){
           switch($NewData['type']){
               case 90001:
                //绑定群
                   Gateway::joinGroup($client_id,$NewData['id']);
                   $date = ['code'=>'ok'];
                    Gateway::sendToGroup($NewData['id'], json_encode($date));
                   return;
               case 80001:
                //文本信息
                   $text = nl2br(htmlspecialchars($NewData['data']));
                   if(mb_strlen($text) > 250){
                       $date=['type'=>'error','msg'=>'50001'];
                       Gateway::sendToClient($client_id, json_encode($date));
                       return ;
                   }
                   $m_ToUserID = $NewData['m_FromUserID'];
                   $date=['type'=>80001, 'data'=>$text, 'm_FromUserID'=>$m_ToUserID, 'time'=>time()];
                   Gateway::sendToGroup($NewData['id'], json_encode($date));
                   Gateway::sendToAll(json_encode(['type'=>'list','time'=>time()]));
                   return;
               case 80002:
                   $text = nl2br(htmlspecialchars($NewData['data']));
                   if(mb_strlen($text) > 250){
                       $date=['type'=>'error','msg'=>'50002'];
                       Gateway::sendToUid($NewData['id'], json_encode($date));
                       return ;
                   }
                   $m_ToUserID = $NewData['m_FromUserID'];
                   $date=['type'=>80002, 'data'=>$text, 'm_FromUserID'=>$m_ToUserID, 'time'=>time()];
                   Gateway::sendToUid($NewData['id'], json_encode($date));
                   Gateway::sendToAll(json_encode(['type'=>'list','time'=>time()]));
                   return;
               case 80003:
                   $text = nl2br(htmlspecialchars($NewData['data']));
                   if(mb_strlen($text) > 250){
                       $date=['type'=>'error','msg'=>'50003'];
                       Gateway::sendToUid($NewData['id'], json_encode($date));
                       return ;
                   }
                   $m_ToUserID = $NewData['m_FromUserID'];
                   $date=['type'=>80003, 'data'=>$text, 'm_FromUserID'=>$m_ToUserID, 'time'=>time()];
                   Gateway::sendToUid($NewData['id'], json_encode($date));
                   Gateway::sendToAll(json_encode(['type'=>'list','time'=>time()]));
                   return;
               case 80004:
                   $text = nl2br(htmlspecialchars($NewData['data']));
                   if(mb_strlen($text) > 250){
                       $date=['type'=>'error','msg'=>'50003'];
                       Gateway::sendToUid($NewData['id'], json_encode($date));
                       return ;
                   }
                   $m_ToUserID = $NewData['m_FromUserID'];
                   $date=['type'=>80004, 'data'=>$text, 'm_FromUserID'=>$m_ToUserID, 'time'=>time()];
                   Gateway::sendToUid($NewData['id'], json_encode($date));
                   Gateway::sendToAll(json_encode(['type'=>'list','time'=>time()]));
                   return;

           }
       }else{
           Gateway::sendToAll(json_encode(['type'=>'list','time'=>time()]));
       }

   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       // 向所有人发送 
      //  GateWay::sendToAll("$client_id logout\r\n");
   }
}
