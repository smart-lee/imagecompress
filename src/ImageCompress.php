<?php
namespace imagecompress;

class ImageCompress
{
    /**
     * @description 图片压缩
     * 规则，2M以内不压缩，大于2M分阶梯压缩，直至压缩成2M内
     * @param $filename 图片路径，相对路径且包含带后缀的文件名
     * @return array|void 不需要接收返回值，因为压缩成功会替换源文件，且名称一样，还是$filename
     * @author lisl 2023-03-15
     */
    public function compressImage($filename = '')
    {
        $result = [
            'status' => -200,
            'msg' => '',
            'data' => [],
        ];
        try {
            if (empty($filename) || !is_file($filename)) {
                throw new \Exception('要压缩的图片不存在');
            }
            //filesize函数的结果会被缓存，使用此函数清除文件状态缓存
            clearstatcache();
            //filesize函数获得的是字节数，除以1024为KB数，再除以1024为MB数
            $filesize = filesize($filename) / 1024 / 1024;
            $noCompressMb = 2;
            //小于2M不压缩
            if ($filesize <= $noCompressMb) { //Mb数
                throw new \Exception('要压缩的图片未到压缩条件');
            }
            $rand = range(1, 2, 0.1); //随机压缩成1-2M

            //这是第一次提交
            //这是第二次提交

            //i的值影响压缩质量
            if ($filesize > 50) {
                $i = 80;
            } elseif ($filesize > 30) {
                $i = 90;
            } elseif ($filesize > 10) {
                $i = 95;
            } else {
                $i = 99;
            }
            //要压缩的图片路径
            $path = pathinfo($filename);
            $tempDir = $path['dirname']; //临时文件存放文件夹
            $extension = $path['extension'];
            if (!is_dir($tempDir)) { //文件夹不存在，创建
                mkdir($tempDir);
            }
            $tmpFileList = []; //临时文件数组

            do {
                if ($i <= 0) { // 0:当到最差质量，跳出
                    return;
                }
                //不影响响应时间的前提下，参数可以调节
                if ($filesize > 50) {
                    $step = 9;
                } elseif ($filesize > 30) {
                    $step = 8;
                } elseif ($filesize > 10) {
                    $step = 7;
                } else {
                    $step = 5;
                }
                $fileInfo = @getimagesize($filename);
                $type = $fileInfo[2];
                $imgResource = null;
                //return ['jpg', 'jpeg', 'gif', 'bmp', 'png'];
                switch ($type) {
                    case 1:
                        $imgResource = imagecreatefromgif($filename);
                        $func = 'gif';
                        break; // GIF
                    case 2:
                        $imgResource = imagecreatefromjpeg($filename);
                        $func = 'jpg';
                        break; // JPG
                    case 3:
                        $imgResource = imagecreatefrompng($filename);
                        $func = 'png';
                        break; // PNG
                    case 6:
                        $imgResource = imagecreatefrombmp($filename);
                        $func = 'bmp';
                        break; // BMP
                }
                $tmpFile = $tempDir . '/' . md5(uniqid(microtime(true), true)) . '.' . $extension; //临时文件
//                $fun = "image" . $func;
                $fun = "imagejpeg";
                if (function_exists($fun)) {
                    $fun($imgResource, $tmpFile, $i);//保存质量为$i的图片文件
                }
                imagedestroy($imgResource); //销毁图片资源
                $tmpFileList[] = $tmpFile;
                $i -= $step;
                clearstatcache();
                $filesize = filesize($tmpFile) / 1024 / 1024;
            } while ($filesize > $noCompressMb);
            rename($tmpFile, $filename); //压缩好的图片重命名为源图片
            foreach ($tmpFileList as $tmpFile) { //删除临时文件
                if (is_file($tmpFile)) {
                    @unlink($tmpFile);
                }
            }
            $result['status'] = 1;
            $result['msg'] = 'success';
            return $result;
        } catch (\Exception $e) {
            $result['msg'] = $e->getMessage();
            return $result;
        }
    }
}