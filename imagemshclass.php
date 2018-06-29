<?php
/**
 * 图片处理
 *  目前有两个功能函数： 1.上传图片接收、 2.图片生成缩略图
 */
class Imagemshclass {
    function __construct(){
        # code...
    }
    // 保存http上传的文件
    function uploadFile($file_name, $save_path, $new_name=ture, $save_date=false){
        if(!empty($_FILES)){
            // 存储目录
            if($save_date){
                $final_dir = date('Ymd');
                $save_path = $this->unionPath($save_path, $final_dir);
            }
            if (!file_exists($save_path)){
                // throw new Exception("Path (".$save_path.") not exists");
                mkdir($save_path, 0777);
                //echo '创建文件夹'.$save_path;
            }
            if($new_name){
                $new_name = date('YmdHis').$this->getFilePostfix($_FILES['titleimg']["name"]);
            }else{
                $new_name = $_FILES['titleimg']["name"];
            }

            // 最终路径
            $save_file_path = $this->unionPath($save_path, $new_name);
            // $save_file_path = iconv("UTF-8","gb2312", save_file_path);

            $res = move_uploaded_file($_FILES[$file_name]["tmp_name"], $save_file_path);
            if($res)
                return $final_dir.'/'.$new_name;
        }
        return null;
    }

    /**
     * 生成新的尺寸的图片
     * @param filename string 源文件
     * @param width int 新图片宽
     * @param height int 新图片高
     * @param savedir string 新图片存储目录
     * @param deleteimg boolean 是否删除源文件，默认否
     * return string 新文件
     */
    function resizeImages($filename, $width, $height, $savedir, $bgcolor=null, $deleteimg=false){
        if(!file_exists($filename)){return false;}
        //读取原图信息
        list($w, $h, $imgType) = getimagesize($filename);
        $mime=image_type_to_mime_type($imgType);
        $createFun=str_replace('/', 'createfrom', $mime);
        if(!in_array($createFun, array("imagecreatefromgif", "imagecreatefromjpeg", "imagecreatefrompng"))){
            return null;
        }
        $outFun=str_replace('/', '', $mime);

        //填充比——宽高
        if($w/$width > $h/$height){
            $sfb = $w/$width;
            $mb_w = 0;
            $mb_h = ($height-$h/$sfb)/2;
        }else{
            $sfb = $h/$height;
            $mb_w = ($width-$w/$sfb)/2;
            $mb_h = 0;
        }
        $n_w = $w/$sfb;
        $n_h = $h/$sfb;

        //创建拷贝图片资源
        $img = $createFun($filename);
        $newImage = imagecreatetruecolor($width, $height);
        // $background_color1 = imagecolorallocate($newImage, 220, 220, 220);
        // imagefilledrectangle ($newImage, 0, 0, $width, $height, $background_color1);//填充背景色为灰色
        // $background_color2 = imagecolorallocate($newImage, 255, 255, 255);
        // imagefilledrectangle ($newImage, 1, 1, $width-2, $height-2, $background_color2);//填充白色形成白底1像素的灰框画布

        //填充背景色为白色
        if(empty($bgcolor)){
            $background_color = imagecolorallocate($newImage, 255, 255, 255);
        }else{
            $background_color = imagecolorallocate($newImage, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
        }
        imagefilledrectangle ($newImage, 0, 0, $width, $height, $background_color);

        //将图片放入画布
        imagecopyresampled($newImage, $img, $mb_w, $mb_h, 0, 0, $n_w, $n_h, $w, $h);

        //检查目录以及生成新的文件名
        if(!file_exists($savedir)){//如果目录不存在，则，创建之。
            mkdir($savedir, 0777);
        }

        $newName = $savedir.$width.'_'.$height.'_'.basename($filename);

        //生成新的图片并清理占用资源
        $outFun($newImage, $newName);
        imagedestroy($img);
        imagedestroy($newImage);

        //如果必要则删除原图
        if($deleteimg) unlink($filename);

        return $newName;
    }

    // 获取文件后缀名
    function getFilePostfix($name){
    return strrpos($name, '.') ? '.'.end( explode('.', $name) ) : '';
    // if(strrpos($name, '.')){
    //     $list = explode('.', $name);
    //     return '.'.end($list));
    // }else{
    //     return '';
    // }
    }

    // 合并追加目录路径
    function unionPath($base_path, $final_dir){
    return (substr($base_path, -1) == '/') ? $base_path.$final_dir : $base_path.'/'.$final_dir;
    // if(substr($base_path, -1) == '/'){
    //     return $base_path.$final_dir;
    // }else{
    //     return $base_path.'/'.$final_dir;
    // }
    }

    // 创建多级目录
    function mkdirRecursion($dirName, $rights=0777){
        $dirs = explode('/', $dirName);
        $dir='';
        foreach ($dirs as $part) {
            $dir.=$part.'/';
            if (!is_dir($dir) && strlen($dir)>0)
                mkdir($dir, $rights);
        }
    }

    function __destruct() {
       # code...
       }
}
