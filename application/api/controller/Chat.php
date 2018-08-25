<?php
namespace app\api\controller;

use think\Db;
use think\Request;
use think\Controller;

class Chat extends Controller
{
    /**
     * 聊天内容持久化到数据库
     */
    public function saveMessage()
    {
        if(Request::instance()->isAjax()) {
            $message = input("post.");
            $data['from_id']   = $message['fromId'];
            $data['from_name'] = $this->getUserInfo($message['fromId'], 'nickname');
            $data['to_id']     = $message['toId'];
            $data['to_name']   = $this->getUserInfo($message['toId'], 'nickname');
            $data['content']  = $message['data'];
            $data['time']     = $message['time'];
            $data['is_read']   = $message['isread'];
            $data['type']     = 1;
            Db::name('communication')->insert($data);
        }
    }

    /**
     * 初始化消息记录
     *
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function initData()
    {
        if(Request::instance()->isAjax()) {
            $fromId = input('post.fromId');
            $toId   = input('post.toId');
            $map['from_id'] = $fromId;
            $map['to_id']   = $toId;
            $condition['from_id'] = $toId;
            $condition['to_id']   = $fromId;
            $count  = Db::table('communication')->where(function($query) use($map){
                $query->where($map);
            })->whereOr(function($query) use($condition) {
                $query->where($condition);
            })->count();
            $result = Db::table('communication')->where(function($query) use($map) {
                $query->where($map);
            })->whereOr(function($query) use($condition){
                $query->where($condition);
            })->limit($count -10 , 10)->select();
            return $result;
        }
    }

    /**
     * 获取聊天用户头像
     *
     * @return array
     */
    public function getHeader()
    {
        if(Request::instance()->isAjax()) {
            $fromId = input("post.fromId");
            $toId   = input("post.toId");
            $fromHeader = $this->getUserInfo($fromId, 'headerimg');
            $toHeader = $this->getUserInfo($toId, 'headerimg');
            return [
                'fromHeader' => $fromHeader,
                'toHeader'   => $toHeader
            ];
        }
    }

    /**
     * 获取聊天用户姓名
     *
     * @return array
     */
    public function getName()
    {
        if(Request::instance()->isAjax()) {
            $toId = input('post.toId');
            $name = $this->getUserInfO($toId, 'nickname');
            return [
                'nickName' => $name
            ];
        }
    }

    /**
     * 获取聊天用户的信息
     *
     * @param $uid
     * @param $field
     * @return mixed
     */
    private function getUserInfo($uid, $field)
    {
        $data = Db::name('user')->where('id', $uid)->field($field)->find();
        return $data[$field];
    }

    /**
     * 上传聊天图片
     * @return array
     *
     */
    public function uploadImg()
    {
        if(Request::instance()->isAjax()) {
            $file = $_FILES['file'];
            $fromId = input('fromId');
            $toId   =  input('toId');
            $online = input('online');
            $suffix = strtolower(strrchr($file['name'], '.'));
            $type  = ['.jpg', '.jpeg', '.gif', '.png'];
            if(!in_array($suffix, $type)) {
                return['status' => 'img type error'];
            }
            if($file['size']/1024 > 5120) {
                return ['status' => 'image is too large'];
            }
            $filename = uniqid("chat_img_", false);
            $uploadPath = ROOT_PATH.'public\\uploads\\';
            $file_up = $uploadPath.$filename.$suffix;
            $re = move_uploaded_file($file['tmp_name'], $file_up);
            if($re) {
                $name = $filename.$suffix;
                $data['content'] = $name;
                $data['from_id'] = $fromId;
                $data['to_id']   = $toId;
                $data['from_name'] = $this->getUserInfo($fromId, 'nickname');
                $data['to_name']   = $this->getUserInfo($toId, 'nickname');
                $data['time']    = time();
                $data['is_read'] = $online;
                $data['type'] = 2;
                $id = Db::table('communication')->insertGetId($data);
                if($id) {
                    return [
                        'status' => 1,
                        'path' => $filename.$suffix
                    ];
                }else {
                    return [
                        'status' => 0,
                        'path' =>''
                    ];
                }
            }
        }
    }

    /**
     * 未读的消息数目
     *
     * @param $fromId
     * @param $toId
     * @return int|string
     */
    private function getNoReadMsg($fromId, $toId)
    {
        return Db::table('communication')->where(['from_id' => $fromId, 'to_id' => $toId, 'is_read' => 0])->count();
    }

    /**
     * 获取聊天的最近一条信息
     * @param $fromId
     * @param $toId
     * @return array|false|\PDOStatement|string|\think\Model
     */
    private function getLastMsg($fromId, $toId)
    {
        $map['from_id'] = $fromId;
        $map['to_id']   = $toId;
        $condition['from_id'] = $toId;
        $condition['to_id']   = $fromId;
        $res = Db::table('communication')->where(function($query)use($map){
            $query->where($map);
        })->whereOr(function($query)use($condition) {
            $query->where($condition);
        })->field(['content', 'time'])->order('id desc')->limit(1)->find();
        return $res;
    }

    /**
     * 消息列表
     * @return array
     */
    public function getList()
    {
        if(Request::instance()->isAjax()) {
            $fromId = input('post.id');
            $info = Db::table('communication')->field(['id','to_id', 'from_id', 'from_name','content', 'time'])
                ->where(['to_id' => $fromId])->group('from_id')->select();
            $result = array_map(function($res){
                return [
                    'headerImg' => $this->getUserInfo($res['from_id'], 'headerimg'),
                    'fromName'  => $res['from_name'],
                    'noReadMsgCount' => $this->getNoReadMsg($res['from_id'], $res['to_id']),
                    'lastMsg'   => $this->getLastMsg($res['from_id'], $res['to_id'])['content'],
                    'lastTime'  => date('Y-m-d H:i', $this->getLastMsg($res['from_id'], $res['to_id'])['time']),
                    'url'   => 'www.tp5.local/index/index/index?fromId='.$res['to_id'].'&toId='.$res['from_id']
                ];
            }, $info);
            return $result;
        }
    }
}
