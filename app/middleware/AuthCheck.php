<?php
declare (strict_types=1);

namespace app\middleware;

use tauthz\exception\Unauthorized;
use tauthz\facade\Enforcer;
use thans\jwt\exception\TokenBlacklistException;
use thans\jwt\exception\TokenBlacklistGracePeriodException;
use thans\jwt\exception\TokenExpiredException;
use think\facade\Log;
use think\Request;
use think\response\Json;
use Throwable;
use Exception;

class AuthCheck extends BaseMiddleware
{

    /**
     * 处理请求
     * @param \think\Request $request
     * @param \Closure $next
     * @param ...$args
     * @return Response
     * @throws Unauthorized
     */
    public function handle($request, \Closure $next, $args)
    {
        // OPTIONS请求直接返回
        if ($request->isOptions()) {
            return response();
        }

        $tokenKey = $request->header('Authorization');
        if (!$tokenKey) {
            return $this->retAuthFailure('无权访问！');
        }
        $envKey = env('app.token', '19860611');
        // 如果是开发环境,token设置指定key则跳过验证。
        if (config('app.app_debug') && $tokenKey == $envKey) {
            return $next($request);
        }
        $retAuthArr = $this->getAuthzIdentifier($request, $next);
        $authzIdentifier = $retAuthArr['payload'];
        if ($retAuthArr['code'] != 200) {
            return $this->retAuthFailure('无权访问');
        }
        // 对比用户权限
        $user = $authzIdentifier['username']->getValue();
        // 如果一个参数则执行模式
        if (count($args) > 1) {
            if (!Enforcer::enforce($user, ...$args)) {
                return $this->retFailure('无权访问');
            }
        } else {
            $pattern="/\/api\/(.*)/";
            $url = $_SERVER ['REQUEST_URI'];
            if(preg_match($pattern, $url, $arr)){
                if (!Enforcer::enforce($user, $arr[1], $args[0])) {
                    return $this->retFailure('无权访问');
                }
            }

        }

        if (array_key_exists('Authorization', $authzIdentifier)) {
            $headerArr = [
                'Access-Control-Expose-Headers' => 'Authorization',
                'Authorization' => $authzIdentifier['Authorization'],
            ];
            return $next($request)->header($headerArr);
        }
        return $next($request);
    }

    public function getAuthzIdentifier(Request $request, \Closure $next)
    {
        // 1.获取token
        $tokenFull = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $tokenFull);

        // 验证token
        try {
            // 3.验证token,返回Payload
            $this->auth->setToken($token);
            $payload = $this->auth->auth();
            return ["code"=>200,"payload"=>$payload];

        } catch (TokenExpiredException $e) { // 捕获token过期
            // 尝试刷新token
            try {
                $this->auth->setRefresh();
                $token = $this->auth->refresh();
                $payload = $this->auth->auth(false);
                $tokenArr = array_merge($payload, ['Authorization' => 'Bearer ' . $token]);
                return ["code"=>200,"payload"=>$tokenArr];
            } catch (TokenBlacklistGracePeriodException $e) { // 捕获黑名单宽限期
                $payload = $this->auth->auth(false);
                return ["code"=>403,"payload"=>$payload];
            }
        } catch (TokenBlacklistGracePeriodException $e) { // 捕获黑名单宽限期
            $payload = $this->auth->auth(false);
            return ["code"=>403,"payload"=> $payload];
        } catch (Throwable | TokenBlacklistException | Exception $e) { // 捕获黑名单宽限期
            $payload = $this->auth->auth(false);
            Log::error('black');
            return ["code"=>403,"payload"=> $payload];
        }
    }

    /**
     *  失败时返回
     * @param $errMsg
     * @return Json
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

    public function retAuthFailure($errMsg): Json
    {
        $data = [
            "code" => 401,
            "msg" => $errMsg,
            "result" => ""
        ];
        return json($data, 401);
    }


}
