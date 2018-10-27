<?php
namespace app\Upload;

class Upload
{
    public function upload($file)
    {
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(ROOT_PATH.'public'.DS.'uploads');
            if ($info) {
                // 成功上传后 获取上传信息
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                return '\uploads\\'.$info->getSaveName();
            } else {
                // 上传失败获取错误信息
                return $file->getError();
            }
        }
    }
}