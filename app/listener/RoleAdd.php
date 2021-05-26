<?php
declare (strict_types=1);

namespace app\listener;

use tauthz\facade\Enforcer;
use think\facade\Log;

class RoleAdd
{
    /**
     * 事件监听处理
     *  当添加角色时，激活添加权限事件
     * @return mixed
     */
    public function handle($role)
    {
        // 设置所有角色可以修改自己密码
        Enforcer::addPermissionForUser($role->name, 'user/setUserPassWord', 'write');
        // 任何角色都可以增加主条目
        Enforcer::addPermissionForUser($role->name, 'notes/addNotes', 'write');
        // 任何角色都可以访问主条目
        Enforcer::addPermissionForUser($role->name, 'notes/getAllNotes', 'read');
        // 获取主条目角色列表的接口
        Enforcer::addPermissionForUser($role->name, 'notes/getNotesRoles', 'read');
        // 验证主条目密码的接口
        Enforcer::addPermissionForUser($role->name, 'notes/checkPassWord', 'read');
        // 增加密码条目
        Enforcer::addPermissionForUser($role->name, 'items/addItems', 'write');
        // 修改主条目
        Enforcer::addPermissionForUser($role->name, 'notes/updateNotes', 'write');

        // 获取类别数据
        Enforcer::addPermissionForUser($role->name, 'cate/getAllData', 'read');
        // 获取类别数据
        Enforcer::addPermissionForUser($role->name, 'notes/getUserRoles', 'read');

        // 首页信息
        Enforcer::addPermissionForUser($role->name, 'home/info', 'read');

        // 删除items
        Enforcer::addPermissionForUser($role->name, 'items/delete', 'write');


        // 清理缓存
        $this->executeClear();
    }


    /**
     *  调用清理缓存方法
     * @param $cmd
     * @param bool $dir
     * @param false $expire
     *   executeClear('cache') 清理缓存  executeClear('log') 清理缓存
     */
    private function executeClear($cmd = 'cache', $dir = true, $expire = false)
    {
        $runtimePath = root_path() . 'runtime' . DIRECTORY_SEPARATOR;

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
    protected function cacheHasExpired(string $filename): bool
    {
        $content = file_get_contents($filename);
        $expire = (int)substr($content, 8, 12);
        return 0 != $expire && time() - $expire > filemtime($filename);
    }
}
