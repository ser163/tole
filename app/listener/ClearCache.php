<?php
declare (strict_types = 1);

namespace app\listener;

use think\facade\Log;

class ClearCache
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle()
    {
        $this->executeClear();
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




}
