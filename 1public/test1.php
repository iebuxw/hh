<?php
/*$pf = file_get_contents('city(2).json');
echo "<pre>";
$arr = json_decode($pf, true);

foreach ($arr['RECORDS'] as $key => $value) {
    $arr_son[] = $value['sorting'];
}

array_multisort($arr['RECORDS'], SORT_DESC, $arr_son);
print_r($arr['RECORDS']);exit;

file_put_contents('city3.json', json_encode());



function ext_json_decode($str, $mode=false){
    $str = preg_replace('/([{,])(\s*)([A-Za-z0-9_\-]+?)\s*:/','$1"$3":',$str);
    $str = str_replace('\'','"',$str);
    $str = str_replace(" ", "", $str);
    $str = str_replace('\t', "", $str);
    $str = str_replace('\r', "", $str);
    $str = str_replace("\l", "", $str);
    $str = preg_replace('/s+/', '',$str);
    $str = trim($str,chr(239).chr(187).chr(191));

    // return $str;
    return json_decode($str, $mode);
}*/
//自定义一个函数dirSize()，统计传入参数的目录大小
function dirSize($directory){
  $dir_size = 0; //用来累加各个文件大小

  if($dir_handle = @opendir($directory)){      //打开目录，并判断是否能成功打开
    while($filename = readdir($dir_handle)){     //循环遍历目录下的所有文件
        if($filename != "."&& $filename != ".."){     //一定要排除两个特殊的目录
            $subFile = $directory."/".$filename;     //将目录下的子文件和当前目录相连
            if(is_dir($subFile))     //如果为目录
            $dir_size += dirSize($subFile);     //递归地调用自身函数，求子目录的大小
            if(is_file($subFile))     //如果是文件
            $dir_size += filesize($subFile);     //求出文件的大小并累加
        }
    }
    closedir($dir_handle);      //关闭文件资源
    return $dir_size;     //返回计算后的目录大小
  }
}

/*$dir_size = dirSize("phpMyAdmin");    //调用该函数计算目录大小
echo round($dir_size/pow(1024,1),2)."KB";    //字节数转换为“KB”单位并输出*/
/*print_r($_POST);
echo "<br>";
print_r($_GET);
echo "<br>";
print_r($_REQUEST);*/


// echo 122;

$a = '511203199106034578';
$b = '511203199106034579';
if ($a === $b) {
echo 'equal';
} else {
echo 'notEqual';
}

$c = 511203199106034578;
$d = 511203199106034579;
echo $c,$d;

if ('000000cd' == 0) {
    echo '相等';
}else{
    echo '不相等';
}
