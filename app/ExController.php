<?php
declare (strict_types=1);

namespace app;


use app\event\UserAction;
use app\model\User;
use think\App;
use think\exception\HttpResponseException;
use think\facade\Cache;
use think\facade\Log;
use think\Response;
use think\response\Json;
use Hashids\Hashids;
use think\facade\Config;

/**
 * 控制器扩展基础类
 */
class ExController extends BaseController
{

    public ?\think\cache\Driver $Redis = null;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->Redis = Cache::store('redis');
    }

    /**
     *  成功之后返回数据
     *   {
     * code: '0',
     * msg: '',
     * result: {
     * username: '万纯（harrywan）',
     * roleName: '管理员'
     * }
     * }
     * @param $paraData
     * @return Json
     */
    public function retSucceed(array $paraData): Json
    {
        $data = [
            "code" => "0",
            "msg" => "成功",
            "result" => $paraData
        ];
        return json($data, 200);
    }

    /**
     *  成功只有提示信息
     * @param string $message
     * @param int $type (0 成功，1 警告，2 错误)
     * @return Json
     */
    public function retSucceedInfo(string $message, int $type = 0): Json
    {
        $data = [
            "code" => "0",
            "msg" => "成功",
            "result" => [
                'type' => $type,
                'msg' => $message
            ]
        ];
        return json($data, 200);
    }

    /**
     *  带header返回的
     * @param array $paraData
     * @param array $header
     * @return Json
     */
    public function retSucceedHeader(array $paraData, array $header)
    {
        $data = [
            "code" => "0",
            "msg" => "成功",
            "result" => $paraData
        ];
        return json($data, 200, $header);
    }

    /*
     *  失败时返回
     */
    public function retFailure($errMsg)
    {
        $data = [
            "code" => "3",
            "msg" => $errMsg,
            "result" => ""
        ];
        return json($data, 200);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息
     * @param mixed $data 返回的数据
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function success($data = '', $msg = '操作成功', array $header = [])
    {
        $code = 200;

        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];

        $type = $this->getResponseType();
        $header['Access-Control-Allow-Origin'] = '*';
        $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,XX-Device-Type,XX-Token,XX-Api-Version,XX-Wxapp-AppId';
        $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed $data 返回的数据
     * @param integer $count 总数
     * @param mixed $msg 提示信息
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function tableSuccess($count, $data = '', $msg = '操作成功', array $header = [])
    {
        $code = 0;

        $result = [
            'code' => $code,
            'count' => $count,
            'msg' => $msg,
            'data' => $data,
        ];

        $type = $this->getResponseType();
        $header['Access-Control-Allow-Origin'] = '*';
        $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,XX-Device-Type,XX-Token,XX-Api-Version,XX-Wxapp-AppId';
        $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed $msg 提示信息,若要指定错误码,可以传数组,格式为['code'=>您的错误码,'msg'=>'您的错误消息']
     * @param mixed $data 返回的数据
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function error($msg = '', $data = '', array $header = [])
    {
        $code = 0;

        if (is_array($msg)) {
            $code = $msg['code'];
            $msg = $msg['msg'];
        }

        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];

        $type = $this->getResponseType();
        $header['Access-Control-Allow-Origin'] = '*';
        $header['Access-Control-Allow-Headers'] = 'X-Requested-With,Content-Type,XX-Device-Type,XX-Token,XX-Api-Version,XX-Wxapp-AppId';
        $header['Access-Control-Allow-Methods'] = 'GET,POST,PATCH,PUT,DELETE,OPTIONS';
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        return 'json';
    }

    /**
     *  从token中获取用户信息
     * @return
     */
    public function getTokenUserArr()
    {
        $tokenKey = $this->request->header('Authorization');
        if (!$tokenKey) {
            return null;
        }
        $token = str_replace('Bearer ', '', $tokenKey);
        $tokenArr = explode('.', $token);
        $userArr = base64_decode($tokenArr[1]);
        return json_decode($userArr, true);
    }

    /**
     * @param $actCode
     * @param string $args
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sendEvent($actCode, string $args)
    {
        if (config('app.app_debug')) {
            // 从token获取用户
            $userArr = $this->getTokenUserArr();
            if (!$userArr) {
                return $this->retFailure("非法请求");
            }
            $user = (new User)->find($userArr['id']);
            event('UserAction', new UserAction($user, $actCode, $args));
        }
    }

    /**
     *  从token中获取用户信息
     * @return array|\think\Model|Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTokenUser()
    {
        // 从token获取用户
        $userArr = $this->getTokenUserArr();
        if (!$userArr) {
            return $this->retFailure("非法请求");
        }
        $user = (new User)->find($userArr['id']);
        return $user;
    }

    /**
     *  调用清理缓存方法
     * @param $cmd
     * @param bool $dir
     * @param false $expire
     *   executeClear('cache') 清理缓存  executeClear('log') 清理缓存
     */
    public function executeClear($cmd = 'cache', $dir = true, $expire = false)
    {
        $runtimePath = $this->app->getRootPath() . 'runtime' . DIRECTORY_SEPARATOR;

        if ($cmd == 'cache') {
            $path = $runtimePath . 'cache';
        } elseif ($cmd == 'log') {
            $path = $runtimePath . 'log';
        } else {
            $path = $cmd == 'path' ?: $runtimePath;
        }

        $rmdir = $dir ? true : false;
        // --expire 仅当 --cache 时生效
        $cache_expire = $expire == true && $cmd == 'cache';
        $this->clear(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $rmdir, $cache_expire);
        Log::info("清理缓存成功");
    }

    /**
     *  清理缓存
     * @param string $path
     * @param bool $rmdir
     * @param bool $cache_expire
     */
    protected function clear(string $path, bool $rmdir, bool $cache_expire): void
    {
        $files = is_dir($path) ? scandir($path) : [];

        foreach ($files as $file) {
            if ('.' != $file && '..' != $file && is_dir($path . $file)) {
                $this->clear($path . $file . DIRECTORY_SEPARATOR, $rmdir, $cache_expire);
                if ($rmdir) {
                    @rmdir($path . $file);
                }
            } elseif ('.gitignore' != $file && is_file($path . $file)) {
                if ($cache_expire) {
                    if ($this->cacheHasExpired($path . $file)) {
                        unlink($path . $file);
                    }
                } else {
                    unlink($path . $file);
                }
            }
        }
    }

    /**
     * 缓存文件是否已过期
     * @param $filename string 文件路径
     * @return bool
     */
    protected function cacheHasExpired($filename)
    {
        $content = file_get_contents($filename);
        $expire = (int)substr($content, 8, 12);
        return 0 != $expire && time() - $expire > filemtime($filename);
    }

    /**
     *  加密hasId
     * @param $id
     */
    public function hashEn($id): string
    {
        $salt = Config::get('jwt.secret');
        $hashids = new Hashids($salt,16);
        return $hashids->encode($id);
    }

    /**
     *  hashId解密
     * @param $hashStr
     * @return array
     */
    public function hashDe($hashStr): array
    {
        $salt = Config::get('jwt.secret');
        $hashids = new Hashids($salt,16);
        return $hashids->decode($hashStr);
    }

}
