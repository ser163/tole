<?php

namespace app\api\repository;

use app\model\Items;
use fanxd\repository\Repository;
use think\facade\Config;

/**
 * Class RoleRepository
 */
class ItemsRepository extends Repository
{
    public function model()
    {
        return Items::class;
    }


    /**
     * 删除本地资源文件
     * @param array $fileList
     */
    public function delLocalFile(array $fileList)
    {
        $localPath = root_path() . "runtime/storage" . DIRECTORY_SEPARATOR;
        if ($fileList) {
            foreach ($fileList as $file) {
                $fileName = $localPath . $file;
                unlink($fileName);
            }
        }
    }

    /**
     * 根据redis key生成url
     * @param $key
     */
    public function downloadUrl($key, $file = true)
    {
        $site = Config::get('app.site_url');
        $url = $site;
        if ($file) {
            $url .= '/api/file/fileDown/' . $key;
        } else {
            $url .= '/api/file/imageDown/' . $key;
        }
        return $url;
    }

}
