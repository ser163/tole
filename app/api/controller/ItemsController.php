<?php

namespace app\api\controller;

use app\api\repository\ItemsRepository;
use app\api\transform\ItemsTransform;
use app\ExController;
use app\model\Accessory;
use app\model\Activitys;
use app\model\Goods;
use app\model\Items;
use app\model\Records;
use think\App;
use app\model\Notes;
use think\db\exception\PDOException;
use think\facade\Config;
use think\facade\Db;
use \think\response\Json;


/**
 * Class RoleController
 * @package app\api\controller
 */
class ItemsController extends ExController
{
    /**
     * @var ItemsRepository
     */
    protected $repository;

    /**
     * @var ItemsTransform
     */
    protected $transform;

    /**
     * RoleController constructor.
     * @param ItemsRepository $repository
     * @param ItemsTransform $transform
     * @param $app
     */
    public function __construct(ItemsRepository $repository, ItemsTransform $transform, App $app)
    {
        parent::__construct($app);
        $this->repository = $repository;
        $this->transform = $transform;
    }

    /**
     * 获取列表项
     */
    public function getItems($id)
    {
        $noteId = $id;
        $size = input('size');
        $current = input('current');
        if (!$noteId) {
            return $this->retSucceedInfo('缺少ID', 2);
        }
        $notes = Notes::find($noteId);

        if (!$notes) {
            return $this->retSucceedInfo('未找到相关记录', 2);
        }

        // 如果加密则验证密码
        if ($notes->protect) {
            $password = input('pass');
            if (!$password) {
                return $this->retSucceedInfo('缺少密码', 2);
            }
            // 验证密码

            $salt = $notes->show_salt;
            $oldPass = $notes->show_word;

            $en_pass = keyPassEncry($password, $salt);

            if ($oldPass != $en_pass) {
                return $this->retSucceedInfo('密码错误！', 2);
            }
        }

        $itemInfo = Db::name('items')
            ->alias('a')
            ->join('user u', 'a.user_id = u.id')
            ->where('a.note_id', '=', $notes->id)
            ->field('a.id,name,desc,user_id,note_id,a.created,tags,u.username')
            ->order('id', 'desc')
            ->paginate([
                'list_rows' => $size ?: 10,
                'page' => $current ?: 1,
            ])
            ->toArray();
        // 转换hashId
        foreach ($itemInfo['data'] as &$item) {
            $item['id'] = $this->hashEn($item['id']);
        }
        unset($item);

        $itemInfo['type'] = 0;
        $itemInfo['total_page'] = (int)ceil($itemInfo['total'] / $itemInfo['per_page']);
        return $this->retSucceed($itemInfo);
    }

    /**
     *  增加新的附加条目
     */
    public function addItems()
    {
        $noteId = input('note_id');
        $userId = input('user_id');

        $name = input('name');
        $desc = input('desc');

        $list = input('list');
        if (empty($name)) {
            return $this->retSucceedInfo('项目名称不能为空', 2);
        }

        if (count($list) == 0) {
            return $this->retSucceedInfo('至少要有一项附加条目', 2);
        }

        $user = $this->getTokenUser();

        // 对比用户
        if ($userId != $user->id) {
            return $this->retSucceedInfo('传入用户错误', 2);
        }
        // 判断主条目存在
        $note = Notes::find($noteId);
        if (!$note) {
            return $this->retSucceedInfo('主条目ID未找到', 2);
        }

        $data = [
            'name' => $name,
            'desc' => $desc,
            'user_id' => $userId,
            'note_id' => $noteId,
            'tags' => ''
        ];
        $retItem = $this->repository->create($data);

        // 建立分条目
        // 1.加工分条目数据
        /**
         *   前端返回格式
         *   name: '用户名',
         *   seq: 0,
         *   type: 0,
         *   value: '',
         *   up:0,
         *   ext:''
         *   ===以下是增加字段===
         *   item_id （条目id）
         *   salt （加密salt）
         *   accessory_id （此资源id应该上传时返回）
         */
        foreach ($list as &$item) {
            $item['item_id'] = $retItem->id;
            $type = $item['type'];
            if (in_array($type, [0, 1, 2, 3])) {
                $salt = RoundGenerate();
                $item['salt'] = $salt;
                // 解密函数使用keyPassDecode
                $item['value'] = keyPassEncry($item['value'], $salt);
            }
//            if (in_array($type, [4, 5])) {
//
//            }
        }
        unset($item);

        $good = new Goods;
        $good->saveAll($list);

        // 将团队写入项目动态
        // 团队项目和未保护的项目
        if ($note->flag == 1 && $note->protect == 0) {
            $actData = [
                'note_id' => $note->id,
                'user_id' => $user->id,
                'item_id' => $retItem->id,
                'username' => $user->username,
                'note_name' => $note->name,
                'item_name' => $retItem->name
            ];
            $activity = new Activitys;
            $activity->save($actData);
        }

        return $this->retSucceedInfo('新建附加条目成功');
    }

    /**
     *  获取
     * @param $id
     * @param $hash
     */
    public function ItemInfo($id, $hash)
    {
        $note_id = $id;
        $note = Notes::find($note_id);

        if (!$note) {
            return $this->retSucceed('主条目id不存在', 2);
        }

        $itemArr = $this->hashDe($hash);
        if (count($itemArr) > 0) {
            $itemId = $itemArr[0];
        } else {
            return $this->retSucceed('hashId解析失败', 2);
        }

        $itemData = Items::find($itemId);

        if (!$itemData) {
            return $this->retSucceed('附加条目没有找到', 2);
        }

        $goodsList = [];
        $Goods = Goods::where('item_id', $itemId)->order('seq', 'asc')->select();

        if (!$Goods) {
            return $this->retSucceed('附加条目明细没有找到', 2);
        }

        $user = $this->getTokenUser();
        if (!$user) {
            return $this->retSucceedInfo('获取用户失败', 2);
        }

        foreach ($Goods as $item) {
            $tmpArr = [];
            $tmpArr['id'] = $item->id;
            $tmpArr['type'] = $item->type;
            $tmpArr['name'] = $item->name;
            if (in_array($item->type, [0, 1, 2, 3])) {
                $tmpArr['value'] = keyPassDecode($item->value, $item->salt);
            } else {
                $tmpArr['value'] = $item->value;
            }

            $tmpArr['seq'] = $item->seq;
            $tmpArr['salt'] = '';
            $tmpArr['up'] = $item->up;
            $tmpArr['ext'] = $item->ext;

            // 文件下载和图片下载，根据文件类型生成下载链接
            if ($item->type == 4) {
                $roundKey = RoundLongGenerateKey();
                // 生成随机key
                $key = 'FILE:' . $roundKey;
                $this->Redis->set($key, $item->id, 600);
                $tmpArr['url'] = $this->repository->downloadUrl($roundKey);
            } elseif ($item->type == 5) {
                $roundKey = RoundLongGenerateKey();
                // 生成随机key
                $key = 'IMAGE:' . $roundKey;
                $this->Redis->set($key, $item->id, 600);
                $tmpArr['url'] = $this->repository->downloadUrl($roundKey, false);
            } else {
                $tmpArr['url'] = '';
            }

            array_push($goodsList, $tmpArr);
        }
        $owner = $user->id == $itemData->user_id ? 1 : 0;
        $data = [
            'type' => 0,
            'data' => [
                'id' => $hash,
                'name' => $itemData->name,
                'desc' => $itemData->desc,
                'owner' => $owner,
                'list' => $goodsList,
                'username' => $itemData->user->username,
                'created' => $itemData->created
            ]
        ];

        // 当浏览者不是作者时，记录浏览
        if ($owner == 0) {
            $recordData = [
                'visit' => $user->id,
                'owner' => $itemData->user_id,
                'item_id' => $itemData->id
            ];

            $record = new Records;
            $record->save($recordData);
        }

        return $this->retSucceed($data);
    }

    /**
     * 删除items
     */
    public function delete()
    {
        // 对比是否是所有者
        $id = input('id');
        $hashArr = $this->hashDe($id);

        if (!$hashArr) {
            return $this->retSucceedInfo('hashId非法', 2);
        }
        $itemId = $hashArr[0];
        $itemData = Items::find($itemId);

        if (!$itemData) {
            return $this->retSucceedInfo('未找到附加项目', 2);
        }
        $user = $this->getTokenUser();

        if ($itemData->user_id != $user->id) {
            return $this->retSucceedInfo('只有创建者才能删除附加条目', 2);
        }
        // 1.删除关联的附件。
        $goodList = Goods::where('item_id', $itemId)->select();
        $accIdArr = [];
        $goodIdList = [];
        foreach ($goodList as $good) {
            if ($good->type == 4 || $good->type == 5) {
                if ($good->accessory_id) {
                    array_push($accIdArr, $good->accessory_id);
                }
            }
            array_push($goodIdList, $good->id);
        }

        // 判断是否有附件id，如果有执行删除动作
        if (count($accIdArr) > 0) {
            // 等到附件
            $accItem = Accessory::where('id', 'IN', $accIdArr)->select();
            $accFileList = [];
            foreach ($accItem as $acc) {
                // 如果是本地存储
                if ($acc->cloud == 0) {
                    array_push($accFileList, $acc->path);
                } else {
                    //如果是网络存储则执行
                }
            }

            // 如果包含则进行删除。
            if (count($accFileList)) {
                $this->repository->delLocalFile($accFileList);
            }

            // 删除Accessory
            Accessory::destroy($accIdArr);
        }
        // 2.删除关联的goods
        Goods::destroy($goodIdList);
        // 3. 删除items
        $itemData->delete();
        return $this->retSucceedInfo('附加条目删除成功');
    }

    /**
     *  修改
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update($id, $hash)
    {
        $hashArr = $this->hashDe($hash);
        if (!$hashArr) {
            return $this->retSucceedInfo('解析id失败');
        }

        $itemId = $hashArr[0];
        $noteId = input('note_id');
        $name = input('name');
        $desc = input('desc');
        $list = input('list');
        if (empty($name)) {
            return $this->retSucceedInfo('项目名称不能为空', 2);
        }

        if (count($list) == 0) {
            return $this->retSucceedInfo('至少要有一项附加条目', 2);
        }

        $user = $this->getTokenUser();
        $itemData = Items::find($itemId);
        if (!$itemData) {
            return $this->retSucceedInfo('未找到附加条目', 2);
        }
        // 对比用户
        if ($itemData->user_id != $user->id) {
            return $this->retSucceedInfo('只有创建用户才能修改', 2);
        }

        // 判断主条目存在
        $note = Notes::find($noteId);
        if (!$note) {
            return $this->retSucceedInfo('主条目ID未找到', 2);
        }

        $data = [
            'name' => $name,
            'desc' => $desc,
        ];

        $retItem = $this->repository->update(['id' => $itemId], $data);

        // 建立分条目
        // 1.加工分条目数据
        /**
         *   前端返回格式
         *   name: '用户名',
         *   seq: 0,
         *   type: 0,
         *   value: '',
         *   up:0,
         *   ext:''
         *   ===以下是增加字段===
         *   item_id （条目id）
         *   salt （加密salt）
         *   accessory_id （此资源id应该上传时返回）
         */

        /**
         *  以下代码我写的有点乱七八糟的。期待以后有更好的写法。
         */

        $oldGoodList = Goods::where('item_id', $itemId)->select();

        // 先获取原来的条目id
        $oldGoodIdArr = [];
        // 原来ID对照表
        $exsitaccIdArr = [];
        foreach ($oldGoodList as $good) {
            if (in_array($good->type, [4, 5])) {
                array_push($exsitaccIdArr, $good->accessory_id);
            }
            array_push($oldGoodIdArr, $good->id);
        }

        // 原来的项目，做修改操作
        $oldList = [];
        // 把id提取出来，留着做删除对比
        $oldListId = [];
        // 删掉的列表，做删除操作。
        $delListid = [];
        // 新添加的做插入
        $newList = [];
        foreach ($list as &$item) {
            $item['item_id'] = $itemId;
            // 去除无用字段
            if (isset($item['url'])) {
                unset($item['url']);
            }

            $type = $item['type'];
            if (in_array($type, [0, 1, 2, 3])) {
                $salt = RoundGenerate();
                $item['salt'] = $salt;
                // 解密函数使用keyPassDecode
                $item['value'] = keyPassEncry($item['value'], $salt);
            }

            if (isset($item['id'])) {
                // 旧id分选出来
                array_push($oldList, $item);
                array_push($oldListId, $item['id']);
            } else {
                array_push($newList, $item);
            }
        }
        unset($item);

        // 如果有新的新建的。
        if (count($newList) > 0) {
            $good = new Goods;
            $good->saveAll($newList);
        }
        // 对比获得删掉的id
        $delListid = array_diff($oldGoodIdArr, $oldListId);
        if (count($delListid) > 0) {
            $accDelList = [];
            $goodList = Goods::where('id', $delListid)->column('accessory_id');
            foreach ($goodList as $good) {
                $type = $good->type;
                if (in_array($type, [4, 5])) {
                    if ($good->accessory_id) {
                        array_push($accDelList, $good->accessory_id);
                    }
                }
            }
            // 删除goods
            Goods::destroy($delListid);
            // 如果有附件则执行删除附件动作
            if (count($accDelList) > 0) {
                $accItem = Accessory::where('id', 'IN', $accDelList)->select();
                $accFileList = [];
                foreach ($accItem as $acc) {
                    // 如果是本地存储
                    if ($acc->cloud == 0) {
                        array_push($accFileList, $acc->path);
                    } else {
                        //如果是网络存储则执行
                    }
                }

                // 如果包含则进行删除。
                if (count($accFileList)) {
                    $this->repository->delLocalFile($accFileList);
                }
                Accessory::destroy($accDelList);
            }
        }

        // 执行更新旧的
        if (count($oldList) > 0) {
            // 批量执行修改good
            $goodObj = new Goods;
            $goodObj->saveAll($oldList);

            $newAccList = Goods::where('item_id', $itemId)->column('accessory_id');
            $delAccIdArr = array_diff($exsitaccIdArr, $newAccList);
            if (count($delAccIdArr) > 0) {
                Accessory::destroy($delAccIdArr);
            }
        }
        return $this->retSucceedInfo('修改附加条目成功');
    }

}
