<?php
declare (strict_types=1);

namespace app\api\controller;

use app\ExController;
use app\model\Goods;
use ZipArchive;
use think\file;
use think\facade\Db;

class FileController extends ExController
{

    /**
     *  上传文件接口
     * @return \think\response\Json
     * @throws \Exception
     */
    public function upload()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 通过url传递当前索引值
        $index = request()->get('index');
        if (is_null($index)) {
            return $this->retSucceedInfo('索引参数丢失', 2);
        }
        $salt = RoundGenerate();

        // 获取基本信息
        // 获取文件原来的扩展名
        $extName = $file->getOriginalExtension();
        // 获取文件名
        $fileName = $file->getFilename();
        // 获取文件目录
        $path = $file->getPath();

        $zipOverFile = $this->zipFile($fileName, $path, $extName, $salt);

        // 重新生成thinkFile
        $objFile = new file($zipOverFile);
        // 检测文件格式
        $type = in_array($extName, ['png', 'jpg', 'jpeg', 'gif', 'svg', 'bmp']) ? 0 : 1;

        // 上传到本地服务器
        // 如果是阿里云传入oss
        //$savename = \think\facade\Filesystem::disk('oss')->putFile( 'topic', $file);
        // public目录
        // $savename = \think\facade\Filesystem::disk('public')->putFile('topic', $file);

        // local本地目录
        $savename = \think\facade\Filesystem::disk('local')->putFile('file', $objFile);

        // 写入资源数据库

        $AccData = [
            'path' => $savename,
            'cloud' => 0,
            'file_name' => $fileName,
            'ext' => 'zip',
            // type(类型image 0,file 1)
            'type' => $type
        ];

        $acId = Db::name('accessory')->insertGetId($AccData);

        // 删除原来的zip文件
        unlink($zipOverFile);

        $data = [
            'index' => $index,
            'url' => $savename,
            'salt' => $salt,
            'acid' => $acId,
            'ext' => $extName
        ];

        return $this->retSucceed($data);
    }


    /**
     *  上传文件接口
     * @return \think\response\Json
     * @throws \Exception
     */
    public function imageUpload()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 通过url传递当前索引值
        $index = request()->get('index');
        if (is_null($index)) {
            return $this->retSucceedInfo('索引参数丢失', 2);
        }
        $salt = RoundGenerate();

        // 获取基本信息
        // 获取文件原来的扩展名
        $extName = $file->getOriginalExtension();
        // 获取文件名
        $fileName = $file->getFilename();
        // 获取文件目录
        $path = $file->getPath();

        $zipOverFile = $this->zipFile($fileName, $path, $extName, $salt);

        // 重新生成thinkFile
        $objFile = new file($zipOverFile);
        // 检测文件格式
        $type = in_array($extName, ['png', 'jpg', 'jpeg', 'gif', 'svg', 'bmp']) ? 0 : 1;

        // 上传到本地服务器
        // 如果是阿里云传入oss
        //$savename = \think\facade\Filesystem::disk('oss')->putFile( 'topic', $file);
        // public目录
        // $savename = \think\facade\Filesystem::disk('public')->putFile('topic', $file);

        // local本地目录
        $savename = \think\facade\Filesystem::disk('local')->putFile('image', $objFile);

        // 写入资源数据库

        $AccData = [
            'path' => $savename,
            'cloud' => 0,
            'file_name' => $fileName,
            'ext' => 'zip',
            // type(类型image 0,file 1)
            'type' => $type
        ];

        $acId = Db::name('accessory')->insertGetId($AccData);

        // 删除原来的zip文件
        unlink($zipOverFile);

        $data = [
            'index' => $index,
            'url' => $savename,
            'salt' => $salt,
            'acid' => $acId,
            'ext' => $extName
        ];

        return $this->retSucceed($data);
    }

    /**
     *  压缩文件
     * @param $file
     * @param $path
     * @param $extName
     * @param $salt
     */
    public function zipFile($file, $path, $extName, $salt)
    {
        // 拼合基本信息
        // 源文件物理路径
        $originFile = $path . DIRECTORY_SEPARATOR . $file;
        // 压缩文件路径地址
        $zipFullFileName = $originFile . ".zip";
        // zip文件名
        $zipFile = $file . "." . $extName;
        // zip文件内的文件名和路径
        $zipInFileInfo = $zipFile;

        $zipArc = new ZipArchive();
        if ($zipArc->open($zipFullFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            //设置密码 注意此处不是加密,仅仅是设置密码
            if (!$zipArc->setPassword($salt)) {
                throw new RuntimeException('Set password failed');
            }

            //设置压缩包的注释,将文件名扩展名写进注释
            $zipArc->setArchiveComment($zipFile . "," . $extName);
            // $zipArc->getArchiveComment();//获取压缩包的注释
            // $zipArc->getFromName('index.html');//获取压缩包文件的内容

            //往压缩包内添加文件
            $zipArc->addFile($originFile, $zipInFileInfo);

            //加密文件 此处文件名及路径是压缩包内的
            if (!$zipArc->setEncryptionName($zipInFileInfo, ZipArchive::EM_AES_256)) {
                throw new RuntimeException('Set encryption failed');
            }
            $zipArc->close();
        }
        // 返回压缩后的文件路径
        return $zipFullFileName;
    }

    /**
     *  文件下载链接
     * @param $id
     */
    public function fileDown($id)
    {
        $roundKey = $id;
        // 生成随机key
        $key = 'FILE:' . $roundKey;
        $goodsId = $this->Redis->get($key);
        if (is_null($goodsId)) {
            echo "<h2>链接时间只有5分钟，链接已经失效，请重新打开密码窗口，再次生成新链接</h2>";
            exit(0);
        }
        $goodItem = Goods::find($goodsId);

        $accModel = $goodItem->accessorys;
        // 判断是不是网络存储
        if ($goodItem->accessorys->cloud == 0) {
            $zipFile = root_path() . "runtime/storage" . DIRECTORY_SEPARATOR . $accModel->path;
            if (!is_file($zipFile)) {
                echo "<h2>资源文件打开失败，请联系管理员！</h2>";
                exit(0);
            }

            $content = $this->unZipFile($zipFile, $goodItem->salt);
            $finfo = new \finfo(FILEINFO_MIME);
            $mime = $finfo->buffer($content);
//              $mime = mime_content_type($content);
        } else {

        }

        // 设置返回下载header
        $headerDown = [
            // 直接下载
            'Content-Type' => "application/force-download;",
            'Content-Transfer-Encoding' => "binary",
            'Content-Length' => strlen($content),
            'Content-Disposition' => "attachment; filename=\"" . $goodItem->name . "\"",

            // 禁用缓存
            'Expires' => 0,
            'Cache-control' => "private",
            'Pragma' => "no-cache"
        ];

        return response($content, 200, $headerDown)->contentType($mime);
    }

    /**
     *  解压文件
     * @param $path
     * @param $salt
     */
    public function unZipFile($zipFile, $salt)
    {
        $zipArc = new ZipArchive();

        if ($zipArc->open($zipFile) === true) {
            //设置密码 注意此处不是加密,仅仅是设置密码
            if (!$zipArc->setPassword($salt)) {
                throw new RuntimeException('Set password failed');
            }

            //设置压缩包的注释,将文件名扩展名写进注释
            $fileArr = explode(',', $zipArc->getArchiveComment());

        }

        $file = $zipArc->getFromName($fileArr[0]);
        $zipArc->close();
        return $file;
    }

    /**
     *  图片下载链接
     * @param $id
     */
    public function imageDown($id)
    {
        $roundKey = $id;
        // 生成随机key
        $key = 'IMAGE:' . $roundKey;
        $goodsId = $this->Redis->get($key);
        if (is_null($goodsId)) {
            echo "<h2>链接时间只有5分钟，链接已经失效，请重新打开密码窗口，再次生成新链接</h2>";
            exit(0);
        }
        $goodItem = Goods::find($goodsId);

        $accModel = $goodItem->accessorys;

        // 判断是不是网络存储
        if ($goodItem->accessorys->cloud == 0) {
            $zipFile = root_path() . "runtime/storage" . DIRECTORY_SEPARATOR . $accModel->path;
            if (!is_file($zipFile)) {
                echo "<h2>资源文件打开失败，请联系管理员！</h2>";
                exit(0);
            }

            $content = $this->unZipFile($zipFile, $goodItem->salt);
            $finfo = new \finfo(FILEINFO_MIME);
            $mime = $finfo->buffer($content);
//              $mime = mime_content_type($content);
        } else {

        }

        // 设置返回下载header
        $headerDown = [
            // 禁用缓存
            'Expires' => 0,
            'Cache-control' => "private",
            'Pragma' => "no-cache"
        ];

        return response($content, 200, $headerDown)->contentType($mime);
    }

}
