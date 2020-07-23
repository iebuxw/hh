<?php

// 32位
function generateNum() {
    //strtoupper转换成全大写的
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $uuid = substr($charid, 0, 8).substr($charid, 8, 4).substr($charid,12, 4).substr($charid,16, 4).substr($charid,20,12);
    return $uuid;
}

/**
 * 上传到本地 - 简易
 */
function uploadLocal($uploadField = 'upload_file', $fileName = '', $filePath = ''){
    $newPath = $filePath . $fileName;
    mkdirs(dirname($newPath));
    $tmpName = $_FILES[$uploadField]['tmp_name'];

    return move_uploaded_file($tmpName, $newPath);
}


//上传文件调用 - 稍全面
$file = $_FILES['image'];
//允许上传的类型
$allow = array('image/jpeg', 'image/png',  'image/jpg', 'image/gif');
/**
 *文件的上传
 *@param array $file 上传的文件的相关信息（是一个数组有五个元素）
 *@param array $allow 允许文件上传的类型
 *@param string & $error 引用传递，用来记录错误的信息
 *@param string $path 文件上传的目录,不带最后的 /
 *@param int $maxsize = 1024*1024 允许上传的文件大小
 *@return mixed false | $newname 如果上传失败返回false，成功返回文件的新名字
 **/
function upload($file, $allow, &$error, $path, $maxsize =1048576){
    //先判断系统错误
    switch ($file['error']) {
        case 1:
            $error = '上传错误，超出了服务器文件限制的大小!';
            return false;
        
        case 2:
            $error = '上传错误，超出了浏览器表单允许的大小!';
            return false;
 
        case 3:
            $error = '上传错误，文件上传不完整!';
            return false;
 
        case 4:
            $error = '上传错误，请您先选择要上传的文件!';
            return false;
 
        case 6:
        case 7:
            $error = '对不起，服务器繁忙，请稍后再试!';
            return false;
    }

    if ($file['size'] > $maxsize) {
        $error = '上传错误，超出了文件限制的大小！';
        return false;
    }

    if (!in_array($file['type'], $allow)) {
        $error = '上传的文件的类型不正确，允许的类型有：' . implode(',', $allow);
        return false;
    }

    $newname = randName($file['name']); //得到文件新的名字

    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    $target = $path . '/' . $newname;

    $result = move_uploaded_file($file['tmp_name'],  $target);

    if ($result) {
        return $newname;
    }else{
        $error = '发生未知错误，上传失败';
        return false;
    }
}

function formatBytes($bytes)
{
  if ($bytes < 1024) {
      return $bytes . "b";
  } else if ($bytes < 1048576) {
      return round($bytes / 1024, 2) . "kb";
  }
  return round($bytes / 1048576, 2) . 'mb';
}

function exportCsv($data = [], $headlist = [], $fileName) {
    /*输入到CSV文件 解决乱码问题*/
    $tablelength = count($headlist);
    $html = "\xEF\xBB\xBF";

    /*输出表头*/
    foreach ($headlist as $value) {
        $html .= $value . "\t ,";
    }
    $html .= "\n";

    /*输出内容*/
    foreach ($data as $value) {
        for ($i = 0; $i < $tablelength; $i++) {
            $html .= $value[$i] . "\t ,";
        }
        $html .= "\n";
    }

    // $html = mb_convert_encoding($html, "GBK", "UTF-8");//解决乱码
    /*输出CSV文件*/
    $encodedFileName = urlencode($fileName);
    $ua = $_SERVER['HTTP_USER_AGENT'];

    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Type: application/force-download');
    header('Content-Type: application/vnd.ms-execl');
    header('Content-Type: application/octet-stream');
    header('Content-Type: application/download');
    header('Content-Transfer-Encoding: binary');
    
    if (preg_match('/MSIE/', $ua)) {// 解决IE浏览器输出中文名乱码的bug
        header('Content-Disposition: attachmentt; filename="' . $encodedFileName);
    } elseif (preg_match('/Firefox/', $ua)) {
        header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName);
    } else {
        header('Content-Disposition: attachment; filename="' . $fileName);
    }
    
    echo $html;
    exit();
}

/**
 * 导出excel(csv)
 * @data 导出数据
 * @headlist 第一行,列名
 * @fileName 输出Excel文件名
 */
function csv_export($data = array(), $headlist = array(), $fileName) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$fileName.'.csv"');
    header('Cache-Control: max-age=0');
  
    //打开PHP文件句柄,php://output 表示直接输出到浏览器
    $fp = fopen('php://output', 'a');
    
    //输出Excel列名信息
    foreach ($headlist as $key => $value) {
        //CSV的Excel支持GBK编码，一定要转换，否则乱码
        $headlist[$key] = iconv('utf-8', 'gbk', $value);
    }
  
    //将数据通过fputcsv写到文件句柄
    fputcsv($fp, $headlist);
    
    //计数器
    $num = 0;
    
    //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
    $limit = 100000;
    
    //逐行取出数据，不浪费内存
    $count = count($data);
    for ($i = 0; $i < $count; $i++) {
    
        $num++;
        
        //刷新一下输出buffer，防止由于数据过多造成问题
        if ($limit == $num) { 
            ob_flush();
            flush();
            $num = 0;
        }
        
        $row = $data[$i];
        foreach ($row as $key => $value) {
            $row[$key] = iconv('utf-8', 'gbk', $value);
        }

        fputcsv($fp, $row);
    }
}

/**
 * 读取EXCEL文件(第一张表单)
 * @param $filePath:Excel文件全路径
 * @param $needHeader:是否将头作为key
 * @return [['cola' => 'a', 'colb' => 'b'],['cola' => 'c', 'colb' => 'd'], ....] | [['a', 'b'], ['c', 'd'], ....]
 */
function readExcel($filePath, $needHeader = false)
{
    // include '../PHPExcel.php';
    include '../1xiaoyu/PHPExcel.php';
    $result = [];
    $headerNames = [];

    try {
        // 根据类型加载特定读取器
        $excelType = \PHPExcel_IOFactory::identify($filePath);
        $excelReader = \PHPExcel_IOFactory::createReader($excelType);

        $excelReader->setReadDataOnly(true);                // 设置为只读数据，忽略格式
        $excelReader->setLoadSheetsOnly('Sheet1');   // 只读第一个sheet
        $excelFile = $excelReader->load($filePath);         // 载入excel文件
        $excelSheet = $excelFile->getSheet(0);
        $dataRows = $excelSheet->getHighestRow();           // 总行数
        $dataColumns = $excelSheet->getHighestColumn();     // 总列数(不能超过26列)

        if ($needHeader) {
            $headerNames = $excelSheet->rangeToArray('A1:'. $dataColumns . '1', null, true, false, true);
            $headerNames = array_values($headerNames[1]);
        }

        for ($row = ($needHeader ? 2:1); $row <= $dataRows; $row++) {
            $rowData = [];

            for ($columnIdx = 0, $column = 'A'; $column <= $dataColumns; $columnIdx++, $column++) {
                $value = $excelSheet->getCell($column . $row)->getValue();
                if ($needHeader) {
                    $rowData[$headerNames[$columnIdx]] = $value;
                } else {
                    $rowData[] = $value;
                }
            }

            $result[] = $rowData;
        }

        return $result;

        // $path = APPLICATION_PATH.'/public' . $path;
        /*$PHPReader = new PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($filePath)){
            $PHPReader = new PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($filePath)){
                $ret = array("error" => 1, 'msg' => 'Excel cannot be read!');
                return $ret;
            }
        }
        $Sheets = $PHPReader->load($filePath);
        $dataArray = $Sheets->getSheet(0)->toArray();
        $ret = array("error" => 0, 'data' => $dataArray);
        return $ret;*/

    } catch (\Exception $ex) {
        print_r($ex->getMessage());
        // \SeasLog::error($ex->getMessage(), [], LOG_EXCEL);
        return [];
    }
}

/**
 * 写入EXCEL文件(第一张表单)
 * @param $fileName:文件名
 * @param $filePath:Excel文件全路径，必须'/'结尾
 * @param $datas:数据(二维数组)
 * @param $columnHeader:列头名(不能超过26列)
 * @param $output:是否输出到客户端
 * @return boolean
 */
function writeExcel($fileName, $filePath, $datas, $columnHeader = [], $output = false)
{
    include '../PHPExcel.php';
    try {
        $objExcel = new \PHPExcel();
        $objSheet = $objExcel->setActiveSheetIndex();

        $headerNums = count($columnHeader);

        if (!empty($columnHeader)) {
            $objSheet->freezePane('A2');    // 冻结首行
            for ($idx=0, $columnName = 'A'; $idx < $headerNums; $idx++, $columnName++) {
                $objSheet->setCellValue($columnName . '1', $columnHeader[$idx]);
            }
        }

        foreach ($datas as $idx => $data) {
            if ($headerNums > 0) {  // 需要列头
                if (is_array($data) && count($data) == $headerNums) {
                    for ($columnIdx=0, $columnName = 'A'; $columnIdx < $headerNums; $columnIdx++, $columnName++) {
                        $objSheet->setCellValue($columnName . ($idx+2), $data[$columnIdx]);
                    }
                }
            } else { // 无列头
                for ($columnIdx=0, $columnName = 'A'; $columnIdx < count($data); $columnIdx++, $columnName++) {
                    $objSheet->setCellValue($columnName . ($idx+1), $data[$columnIdx]);
                }
            }
        }

        if ($output) {
            $encodedFileName = urlencode($fileName);

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: application/force-download');
            header('Content-Type: application/vnd.ms-execl');
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/download');
            header('Content-Transfer-Encoding: binary');

            $ua = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/MSIE/', $ua)) {
                header('Content-Disposition: attachmentt; filename="' . $encodedFileName);
            } elseif (preg_match('/Firefox/', $ua)) {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName);
            } else {
                header('Content-Disposition: attachment; filename=' . $fileName);
            }

            $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
            $objWriter->save('php://output');

            exit();
        } else {
            $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
            $objWriter->save($filePath . $fileName);
        }

        return true;
    } catch (\Exception $ex) {
        // \SeasLog::error($ex->getMessage(), [], LOG_EXCEL);
        return false;
    }
}

/**
 * 写入pdf文件
 * @param string $fileName 文件名
 * @param string $filePath Excel文件全路径，必须'/'结尾
 * @param string $html htnl数据
 * @param array $cssPath css文件路径，多个文件传数组
 * @param $output:是否输出到客户端
 * @return boolean
 */
function writePDF($fileName, $filePath, $html, $cssPath = '', $output = false)
{
    try {
        require_once('../mpdf-6.0.0/mpdf.php');//暂时这样加载进来
        $mpdf = new \mPDF();

        $mpdf->SetDisplayMode('fullpage');
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;

        if (!empty($cssPath)) {
            $cssPath = is_array($cssPath) ? $cssPath : [$cssPath];

            foreach ($cssPath as $eachPath) {
                $stylesheet = file_get_contents($eachPath);
                $mpdf->WriteHTML($stylesheet,1);
            }
        }

        $mpdf->WriteHTML($html, 2);
        $type = $output ? 'I' : 'f'; // I - 输出; f - 生成文件保存
        $mpdf->Output($filePath . $fileName, $type);

        return true;
    } catch (\Exception $ex) {
        \SeasLog::error($ex->getMessage(), [], LOG_PDF);
        return false;
    }
}

/**
 * 将二维数组按照某列值(不唯一)作为索引输出
 * @param $inputAry : 输入原数组
 * @param $columnName : 值作为key的列名
 * @param $valueColumn : 作为value的字段名
 * @param $needColumnName : 作为value的记录是否需要列名作为key
 * @return array : 输出数组
 */
/*array ( 0 => array ( 'id' => 1, 'name' => 'maomao', 'age' => 30, ), )
变成
array ( 1 => array ( 0 => array ( 'id' => 1, 'name' => 'maomao', 'age' => 30, ), ), )*/
function turnArray2MapMultiValue(array $inputAry, $columnName, array $valueColumn, $needColumnName = false)
{
    $resp = [];

    foreach ($inputAry as $eachRow) {
        $record = [];

        foreach ($valueColumn as $column) {
            if (isset($eachRow[$column])) {
                if ($needColumnName) {
                    $record[$column] = $eachRow[$column];
                } else {
                    $record[] = $eachRow[$column];
                }
            }
        }

        if (!empty($record)) {
            $resp[$eachRow[$columnName]][] = $record;
        }
    }

    return $resp;
}


//以数组的某个唯一值作为键，例如id
function turnArray2Map(array $inputAry, $columnName)
{
    $resp = [];

    foreach ($inputAry as $eachRow) {
        $resp[ $eachRow[$columnName] ] = $eachRow;
    }

    return $resp;
}

//file_get_contents发起请求
function _file_get_contents(){
    $content = stream_context_create(['http' => ['timeout' => 3]]);
    $resp_ip = @file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip={$req_ip}", false, $content);
}

function is_post()
{
    return !!($_SERVER['REQUEST_METHOD'] == 'POST');
}

function get_client_ip($type = 0){
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}


#获取目录大小
function directory_size($directory) {  
  $directorySize=0;  
  if ($dh = @opendir($directory)) {  
     while (($filename = readdir ($dh))) {  
       if ($filename != "." && $filename != "..") {  
         if (is_file($directory."/".$filename)){  
            $directorySize += filesize($directory."/".$filename);  
         }     
         if (is_dir($directory."/".$filename)){  
            $directorySize += directory_size($directory."/".$filename);  
         }  
       }  
    }  
  }  
  @closedir($dh);
  return $directorySize;  
}

//获取来源地址
function referer(){
    $temp = 'HTTP_' . strtoupper(str_replace('-', '_', 'REFERER'));
    if (!empty($_SERVER[$temp])) return $_SERVER[$temp];

    if (function_exists('apache_request_headers'))
    {
        $headers = apache_request_headers();
        if (!empty($headers['REFERER'])) return $headers['REFERER'];
    }
    
    return false;
}
    
function _log_result($logstr, $filename = 'log.txt') {
    $log_dir = "/data/bak/log/";
    $fp = fopen($log_dir . $filename,"a");
    flock($fp, LOCK_EX) ;
    fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time()).": \n".$logstr."\n");
    flock($fp, LOCK_UN);
    fclose($fp);
}

//写日志
function writeLog($params,$file=''){
    $fp = fopen('/Users/qm-216/mycode/lianxi/1xiaoyu/dianru.log','a');
    if(!$fp){
        return ;
    }

    $str = date('Y-m-d H:i:s') . ' ==> ';
    if(is_array($params)) {
        $str .= @var_export($params, true);
    } else {
        $str .= $params;
    }

    $str .= "\n";
    fwrite($fp, $str);
    fclose($fp);
}

function GetRemoteURL($url = '', &$statusCode = 200, $timeout = 0, $header = array()){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, (empty($timeout) ? 5 : $timeout));
    curl_setopt($ch, CURLOPT_HEADER, 0);
    if(!empty($header)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }

    $output = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $output;
}

/**
 * 发送请求
 * @param $url
 * @param array $data
 * @param bool $ispost
 * @param int $timeout
 * @param array $header
 * @return array
 */
function send_request($url, $data = [], $ispost = false, $timeout = 5, $header = []) {
    if (empty($url) || $timeout <= 0) {
        return [];
    }

    $ch = curl_init();//初始化curl

    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        if (is_array($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));//url编码，生成urlencode字符串
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }else{
        if (!empty($data)) {
            $url = $url . '?' . http_build_query($data);
        }
    }
    if(!empty($header)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }

    curl_setopt($ch, CURLOPT_URL, (string)$url);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header,控制是否返回请求头信息
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//不输出到屏幕上，而是返回结果
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);// 对认证证书来源的检查,防止https无数据
    curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);
    $output = curl_exec($ch);//运行curl
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$statusCode, $output];
}

//调试TODO
function dump($str = '')
{
    header("Content-type:text/html;charset=utf-8");
    echo '<pre>';
    (empty($str) || is_bool($str)) ? var_dump($str) : print_r($str);
    echo '</pre>';
    exit();
}

//生成SN,唯一序列号
function make_sn(){
    return md5(uniqid(mt_rand(0,99999999),1));
}

//无限极分类,$items必须处理成以下结构
//
//数组的键和id必须是一致的，
$items = array(
    1 => array('id' => 1, 'pid' => 0, 'name' => '安徽省'),
    2 => array('id' => 2, 'pid' => 0, 'name' => '浙江省'),
    3 => array('id' => 3, 'pid' => 1, 'name' => '合肥市'),
    4 => array('id' => 4, 'pid' => 3, 'name' => '长丰县'),
    5 => array('id' => 5, 'pid' => 1, 'name' => '安庆市'),
);

function gettree($items, $parent_id = 'parent_id', $id = 'id'){
    $tree = array(); //格式化好的树
    if(empty($items)){
        return $tree;
    }
    foreach ($items as $item){
        if (isset($items[$item[$parent_id]])){
            $items[$item[$parent_id]]['son'][] = &$items[$item[$id]];
        }else{
            $tree[] = &$items[$item[$id]];
        }
    }
    return $tree;

}

function i_array_column($input, $columnKey, $indexKey=null){
    if(!function_exists('array_column')){
        $columnKeyIsNumber  = (is_numeric($columnKey))?true:false;
        $indexKeyIsNull            = (is_null($indexKey))?true :false;
        $indexKeyIsNumber     = (is_numeric($indexKey))?true:false;
        $result                         = array();
        foreach((array)$input as $key=>$row){
            if($columnKeyIsNumber){
                $tmp= array_slice($row, $columnKey, 1);
                $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null;
            }else{
                $tmp= isset($row[$columnKey])?$row[$columnKey]:null;
            }
            if(!$indexKeyIsNull){
                if($indexKeyIsNumber){
                  $key = array_slice($row, $indexKey, 1);
                  $key = (is_array($key) && !empty($key))?current($key):null;
                  $key = is_null($key)?0:$key;
                }else{
                  $key = isset($row[$indexKey])?$row[$indexKey]:0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    }else{
        return array_column($input, $columnKey, $indexKey);
    }
}

/**
 * 是否上传文件
 * @return boolean
 */
function _is_uploaded_file($filename)//多文件就是不加[]的名称
{
    $upfiles = isset($filename) ? $_FILES[$filename]['tmp_name'] : $_FILES['file']['tmp_name'];
    if (is_array($upfiles)) {                   //如果是多个文件
        foreach ($upfiles as $upfile) {
            if($upfile) return true;
        }
        return false;
    }
    if(!empty($upfiles)){                       //单个文件
        return true;
    }
    return false;
}

//在树里删除不需要的键
function deleteKey(&$arr, $id, $pid){
    foreach ($arr as $key => &$value) {
        if (!empty($value['child']) && is_array($value['child'])) {
            deleteKey($value['child'], $id, $pid);
        }
        unset($value[$id], $value[$pid]);
    }
}

//多维数组去除空值————版本一(用引用赋值，要先运行函数，再打印被处理的变量，
//因为它是一个处理过程，无返回值。或者加上return返回值，也是可以的)
//
//两个引用
function removeEmpty(&$arr)
{
    foreach ($arr as $key => &$value) {
        if (is_array($value)) {
            removeEmpty($value);
        }

        if (empty($value)) {
            unset($arr[$key]);
        }
    }

    // return $arr;
}

//多维数组去除空值————版本二，有返回值，递归
function removeEmpty2($arr)
{
    foreach ($arr as $key => $value) {
        if (is_array($value)) {
            $arr[$key] = removeEmpty($value);
        }

        if (empty($value)) {
            unset($arr[$key]);
        }
    }

    return $arr;
}

// array_map的妙用————给数组的每个元素拼接'.com'
function addslashes_deep($value)
{
    return is_array($value) ? array_map('addslashes_deep', $value) : $value . '.com';
}


//二维数组排序
function _multi_array_sort($multi_array, $sort_key, $sort=SORT_ASC){
    if(is_array($multi_array)){
        foreach ($multi_array as $row_array){
            if(is_array($row_array)){
                $key_array[] = $row_array[$sort_key];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    array_multisort($key_array, $sort, $multi_array);
    return $multi_array;
}

//二维数组排序
function array_sort($arr, $keys, $type = 'desc')
{
    $key_value = $new_array = array();
    foreach ($arr as $k => $v) {
        $key_value[$k] = $v[$keys];
    }
    if ($type == 'asc') {
        asort($key_value);
    } else {
        arsort($key_value);
    }
    reset($key_value);
    foreach ($key_value as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

/**
 * 友好时间显示
 * @param $time
 * @return bool|string
 */
function friend_date($time)
{
    if (!$time)
        return false;
    $fdate = '';
    $d = time() - intval($time);
    $ld = $time - mktime(0, 0, 0, 0, 0, date('Y')); //得出年
    $md = $time - mktime(0, 0, 0, date('m'), 0, date('Y')); //得出月
    $byd = $time - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')); //前天
    $yd = $time - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
    $dd = $time - mktime(0, 0, 0, date('m'), date('d'), date('Y')); //今天
    $td = $time - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')); //明天
    $atd = $time - mktime(0, 0, 0, date('m'), date('d') + 2, date('Y')); //后天
    if ($d == 0) {
        $fdate = '刚刚';
    } else {
        switch ($d) {
            case $d < $atd:
                $fdate = date('Y年m月d日', $time);
                break;
            case $d < $td:
                $fdate = '后天' . date('H:i', $time);
                break;
            case $d < 0:
                $fdate = '明天' . date('H:i', $time);
                break;
            case $d < 60:
                $fdate = $d . '秒前';
                break;
            case $d < 3600:
                $fdate = floor($d / 60) . '分钟前';
                break;
            case $d < $dd:
                $fdate = floor($d / 3600) . '小时前';
                break;
            case $d < $yd:
                $fdate = '昨天' . date('H:i', $time);
                break;
            case $d < $byd:
                $fdate = '前天' . date('H:i', $time);
                break;
            case $d < $md:
                $fdate = date('m月d日 H:i', $time);
                break;
            case $d < $ld:
                $fdate = date('m月d日', $time);
                break;
            default:
                $fdate = date('Y年m月d日', $time);
                break;
        }
    }
    return $fdate;
}

//获取ip
function getIP()
{
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');
    } elseif (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// 定义一个函数getIP() 客户端IP，
function getIP2(){
    if (getenv("HTTP_CLIENT_IP"))
         $ip = getenv("HTTP_CLIENT_IP");
    else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if(getenv("REMOTE_ADDR"))
         $ip = getenv("REMOTE_ADDR");
    else $ip = "Unknow";

    if(preg_match('/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1 -9]?\d))))$/', $ip))
        return $ip;
    else
        return '';
}

/**
* 自定义函数递归的复制带有多级子目录的目录
* 递归复制文件夹
* @param type $src 原目录
* @param type $dst 复制到的目录
*/
//参数说明：
//自定义函数递归的复制带有多级子目录的目录
function recurse_copy($src, $dst)
{
    $now = time();
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== $file = readdir($dir)) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            }
            else {
                if (file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
                    if (!is_writeable($dst . DIRECTORY_SEPARATOR . $file)) {
                        exit($dst . DIRECTORY_SEPARATOR . $file . '不可写');
                    }
                    @unlink($dst . DIRECTORY_SEPARATOR . $file);
                }
                if (file_exists($dst . DIRECTORY_SEPARATOR . $file)) {
                    @unlink($dst . DIRECTORY_SEPARATOR . $file);
                }
                $copyrt = copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                if (!$copyrt) {
                    echo 'copy ' . $dst . DIRECTORY_SEPARATOR . $file . ' failed<br>';
                }
            }
        }
    }
    closedir($dir);
}
// 递归删除文件夹
function delFile($path,$delDir = FALSE) {
        if(!is_dir($path))
                return FALSE;
    $handle = opendir($path);
    if ($handle) {
        while (false !== ( $item = readdir($handle) )) {
            if ($item != "." && $item != "..")
                is_dir("$path/$item") ? delFile("$path/$item", $delDir) : unlink("$path/$item");
        }
        closedir($handle);
        if ($delDir) return rmdir($path);
    }else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return FALSE;
        }
    }
}

/**
 * 过滤数组元素前后空格 (支持多维数组)
 * @param $array 要过滤的数组
 * @return array|string
 */
function trim_array_element($array){
    if(!is_array($array))
        return trim($array);
    return array_map('trim_array_element',$array);
}

/**
 * 实现中文字串截取无乱码的方法
 */
function getSubstr($string, $start, $length) {
    if(mb_strlen($string,'utf-8')>$length){
      $str = mb_substr($string, $start, $length,'utf-8');
      return $str.'...';
    }else{
      return $string;
    }
}

//php获取中文字符拼音首字母
function getFirstCharter($str){
      if(empty($str))
      {
            return '';
      }
      $fchar=ord($str{0});
      if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
      $s1=iconv('UTF-8','gb2312',$str);
      $s2=iconv('gb2312','UTF-8',$s1);
      $s=$s2==$str?$s1:$str;
      $asc=ord($s{0})*256+ord($s{1})-65536;
     if($asc>=-20319&&$asc<=-20284) return 'A';
     if($asc>=-20283&&$asc<=-19776) return 'B';
     if($asc>=-19775&&$asc<=-19219) return 'C';
     if($asc>=-19218&&$asc<=-18711) return 'D';
     if($asc>=-18710&&$asc<=-18527) return 'E';
     if($asc>=-18526&&$asc<=-18240) return 'F';
     if($asc>=-18239&&$asc<=-17923) return 'G';
     if($asc>=-17922&&$asc<=-17418) return 'H';
     if($asc>=-17417&&$asc<=-16475) return 'J';
     if($asc>=-16474&&$asc<=-16213) return 'K';
     if($asc>=-16212&&$asc<=-15641) return 'L';
     if($asc>=-15640&&$asc<=-15166) return 'M';
     if($asc>=-15165&&$asc<=-14923) return 'N';
     if($asc>=-14922&&$asc<=-14915) return 'O';
     if($asc>=-14914&&$asc<=-14631) return 'P';
     if($asc>=-14630&&$asc<=-14150) return 'Q';
     if($asc>=-14149&&$asc<=-14091) return 'R';
     if($asc>=-14090&&$asc<=-13319) return 'S';
     if($asc>=-13318&&$asc<=-12839) return 'T';
     if($asc>=-12838&&$asc<=-12557) return 'W';
     if($asc>=-12556&&$asc<=-11848) return 'X';
     if($asc>=-11847&&$asc<=-11056) return 'Y';
     if($asc>=-11055&&$asc<=-10247) return 'Z';
     return null;
}

/**
 * 获取整条字符串汉字拼音首字母
 * @param $zh
 * @return string
 */
function pinyin_long($zh){
    $ret = "";
    $s1 = iconv("UTF-8","gb2312", $zh);
    $s2 = iconv("gb2312","UTF-8", $s1);
    if($s2 == $zh){$zh = $s1;}
    for($i = 0; $i < strlen($zh); $i++){
        $s1 = substr($zh,$i,1);
        $p = ord($s1);
        if($p > 160){
            $s2 = substr($zh,$i++,2);
            $ret .= getFirstCharter($s2);
        }else{
            $ret .= $s1;
        }
    }
    return $ret;
}

/**
 *  作用：array转xml
 */
function arrayToXml($arr)
{
    $xml = "<xml>";
    foreach ($arr as $key=>$val)
    {
        if (is_numeric($val))
        {
            $xml.="<".$key.">".$val."</".$key.">";

        }
        else
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    }
    $xml.="</xml>";
    return $xml;
}


/*aes加密
openssl_encrypt(json_encode($bizContent), 'AES-128-ECB', $baseParams['aesKey'], 0, '');


*/

/*签名生成
    （1）生成原始RSA私钥文件rsa_private_key.pem
        openssl genrsa -out rsa_private_key.pem 1024
    （2）将原始的RSA私钥转换为pkcs8模式
        openssl pkcs8 -topk8 -inform PEM -in rsa_private_key.pem -outform PEM -nocrypt -out private_key.pem
    （3）生成RSA公钥 rsa_public_key.pem
        openssl rsa -in rsa_private_key.pem -pubout -out rsa_public_key.pem
*/

/*
生成公钥私钥对的网址，直接点击生成即可
http://web.chacuo.net/netrsakeypair
 */

#签名处理
/*$pem = chunk_split($public_key,64,"\n");//转换为pem格式的公钥  
$pem = "-----BEGIN PUBLIC KEY-----\n" . $content . "-----END PUBLIC KEY-----\n";*/

/**
 * 格式化公钥
 */
function formatPubKey($public_key)
{
    $pem = chunk_split($public_key,64,"\n");
    $pem = "-----BEGIN PUBLIC KEY-----\n" . $content . "-----END PUBLIC KEY-----\n";
    echo $pem;
}

/**
 * 格式化私钥
 */
function formatPriKey($private_key)
{
    $pem = chunk_split($private_key,64,"\n");
    $pem = "-----BEGIN PRIVATE KEY-----\n" . $content . "-----END PRIVATE KEY-----\n";
    echo $pem;
}


//感觉私钥公钥可以传字符串也可以传资源类型
/**
* 签名字符串，私钥签名
* @param $prestr 需要签名的字符串
* return 签名结果
*/
function rsaSign($prestr) {
    $public_key= file_get_contents('/Users/qm-216/mycode/lianxi/1xiaoyu/rsa_private_key.pem');
    $pkeyid = openssl_get_privatekey($public_key);
    openssl_sign($prestr, $sign, $pkeyid);
    openssl_free_key($pkeyid);
    $sign = urlsafe_b64encode($sign);//会有些特殊字符'+','/','='直接拼接到url里面，会被替换或者忽略
    return $sign;
}


//$verify = openssl_verify($prestr, urlsafe_b64decode($sign), $public_key);
/**
* 验证签名，公钥验签
* @param $prestr 需要签名的字符串
* @param $sign 签名结果
* return 签名结果
*/
function rsaVerify($prestr, $sign) {
    $sign = urlsafe_b64decode($sign);
    $public_key= file_get_contents('/Users/qm-216/mycode/lianxi/1xiaoyu/rsa_public_key.pem');
    $pkeyid = openssl_get_publickey($public_key);
    if ($pkeyid) {
        $verify = openssl_verify($prestr, $sign, $pkeyid);
        openssl_free_key($pkeyid);
    }
    if($verify == 1){
        return true;
    }else{
        return false;
    }
}

/**
 * RSA公钥加密，urlsafe_b64encode
 * @param string $key: 公钥
 * @param string $srcStr: 待加密字符串
 * @return string 密文
 */
function rsaPubSafeEncrypt($key, $srcStr){
    $sign = '';
    $res = openssl_pkey_get_public($key);

    if ($res) {
        openssl_public_encrypt($srcStr, $sign, $res);
        openssl_free_key($res);
        $sign = urlsafe_b64encode($sign);
    }

    return $sign ? $sign : '';
}

/**
 * RSA公钥加密，base64_encode
 * @param string $signStr: 待加密字符串
 * @param string $appPubKey: 公钥
 * @return string 密文
 */
function rsaPubEncrypt($signStr, $appPubKey){
    $encrypt = '';
    $pkeyid = openssl_get_publickey($appPubKey);
    if(openssl_public_encrypt($signStr, $encryptedTemp, $pkeyid)) {
        $encrypt = base64_encode($encryptedTemp);
    }

    return $encrypt ? $encrypt : '';
}

//一行也行openssl_public_decrypt(base64_decode($eccryptData), $decrypted, $publicKey);//公钥解密
/**
 * RSA公钥解密
 * @param string $eccryptData: 待解密字符串
 * @param string $publicKey: 公钥
 * @return string 解密后字符串
 */
function pubDecrypt($eccryptData, $publicKey)
{
    $decrypted = "";
    $decodeStr = base64_decode($eccryptData);//如果是urlsafe_b64decode则这里改下
    openssl_public_decrypt($decodeStr, $decrypted, $publicKey);
    return $decrypted ? $decrypted : '';
}

/**
 * RSA私钥加密，base64_encode
 * @param string $signStr: 待加密字符串
 * @param string $appPriKey: 私钥
 * @return string 密文
 */
function rsaPriEncrypt($signStr, $appPriKey){
    $encrypt = '';
    $pkeyid = openssl_get_privatekey($appPriKey);
    if(openssl_private_encrypt($signStr, $encryptedTemp, $pkeyid)) {
        $encrypt = base64_encode($encryptedTemp);
    }

    return $encrypt ? $encrypt : '';
}


//一行也行openssl_private_decrypt(base64_decode($eccryptData), $decrypted, $prikey);//私钥解密
/**
 * RSA私钥解密
 * @param string $eccryptData: 待解密字符串
 * @param string $decryptKey: 私钥
 * @return string 解密后字符串
 */
function priDecrypt($eccryptData, $decryptKey)
{
    $decrypted = "";
    $decodeStr = base64_decode($eccryptData);//如果是urlsafe_b64decode则这里改下
    openssl_private_decrypt($decodeStr, $decrypted, $decryptKey);
    return $decrypted ? $decrypted : '';
}


//万能签名
function getSign(){
    header("Content-type:text/html;charset=utf-8");
    $data = $_POST;        
    unset($data['time']);    // 删除这两个参数再来进行排序     
    unset($data['sign']);    // 删除这两个参数再来进行排序
    ksort($data);
    $str = implode('', $data);        
    $str = $str.$_POST['time'].C('API_SECRET_KEY');        
    return md5($str);
}

function urlsafe_b64encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+','/','='),array('-','_',''),$data);
    return $data;
}

function urlsafe_b64decode($string) {
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}

//小鱼加密解密
//加密 - $this->encrypt(XIAOYU_KEY . $curtime, XIAOYU_ENC_KEY);
//解密 - $this->decrypt($auth_key, XIAOYU_ENC_KEY);
function encrypt($data, $key)
{
    $char = '';
    $str = '';
    $key = md5($key);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= $key{$x};
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
    }
    return $this->urlsafe_b64encode($str);
}

function decrypt($data, $key)
{
    $char = '';
    $str = '';
    $key = md5($key);
    $x = 0;
    $data = $this->urlsafe_b64decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}

class MysqlModel{
    /**
     * 二维数组筛选
     * @param array $arr 要操作的数组
     * @param array $where 条件
     * @param array $order 排序规则
     * @param array $limit 长度
     * @return array
     */
    static protected function arrFilter($arr, $where = array(), $order = array(), $limit = array()){
        $ret_data = array();
        // 筛选
        foreach($arr as $value){
            if( !self::filter($value, $where) ) continue;
            $ret_data[] = $value;
        }

        // 排序
        $ret_data = self::sort($ret_data, $order);

        if( empty($limit) ) return $ret_data;
        if( is_string($limit) ) return array_slice($ret_data, 0);
        return array_slice($ret_data, $limit[0], $limit[1]);
    }

    static protected function filter($data, $where){
        if( empty($data) ) return false;
        if( empty($where) ) return true;

        $s = false;
        foreach($where as $key => $val ){
            $check_val = $data[$key];
            switch(strtolower($val[0])){
                case 'eq' :
                    $s = $check_val == $val[1];
                    break;
                case 'neq' :
                    $s = $check_val != $val[1];
                    break;
                case 'gt' :
                    $s = $check_val > $val[1];
                    break;
                case 'egt' :
                    $s = $check_val >= $val[1];
                    break;
                case 'lt' :
                    $s = $check_val < $val[1];
                    break;
                case 'elt' :
                    $s = $check_val <= $val[1];
                    break;
                case 'in' :
                    $s = in_array($check_val, $val[1]);
                    break;
                case 'notin' :
                    $s = !in_array($check_val, $val[1]);
                    break;
                case 'between' :
                    $s = $check_val >= $val[1][0] && $check_val <= $val[1][1];
                    break;
                case 'notbetween' :
                    $s = !($check_val >= $val[1][0] && $check_val <= $val[1][1]);
                    break;
            }
            if( !empty($val[2]) && strtolower($val[2]) == 'or' ){
                if( $s ) break;
            }else{
                if( !$s ) break;
            }
        }

        return $s;
    }

    static protected function sort($data, $order){
        if( empty($data) ) return array();
        if( empty($order) ) return $data;

        $argc = array();
        foreach( $data as $value ){
            foreach($order as $o => $s){
                $argc[$o][] = $value[$o];
                !isset($argc[$o . $s]) && $argc[$o . $s] = $s;

            }
        }

        $argc = array_values($argc);
        $argc[] = &$data;
        call_user_func_array('array_multisort', $argc);
        return array_pop($argc);
    }
}

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/3/8
 * Time: 下午2:40
 */
class UtilToolModel{

    // 功能:  查询今天剩余的秒数
    static public function getTodayLeft(){
        $now_time = time();
        return strtotime(date("Y-m-d", $now_time + 86400)) - $now_time;
    }

    // 功能：   验证手机号
    static public function isMobile($mobile){
        return !!preg_match('/^(13[0-9]|15[012356789]|17[3678]|18[0-9]|14[57])[0-9]{8}$/', $mobile);
    }

    // 判断是否是数字
    static public function isNum($str){
        return !!preg_match("/^[\d]+$/", $str);
    }

    // 判断是否是中文
    static public function isChina($str){
        return !!preg_match("/^[x7f-xff]+$/", $str);
    }

    // 验证身份证格式
    static public function isIdentity($identity){
        return self::validation_filter_id_card($identity);
    }

    // 函数名:  validation_filter_id_card
    // 功能： 验证身份证格式
    // 作者：  http://blog.sina.com.cn/s/blog_6ec2ae90010154uh.html
    public static function validation_filter_id_card($id_card) {
        if(strlen($id_card) == 18) {
            return self::idcard_checksum18($id_card);
        }elseif((strlen($id_card) == 15)) {
            $id_card = self::idcard_15to18($id_card);
            return self::idcard_checksum18($id_card);
        }else {
            return false;
        }
    }

    // 计算身份证校验码，根据国家标准GB 11643-1999
    public static function idcard_verify_number($idcard_base){
        if(strlen($idcard_base) != 17){
            return false;
        }
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++){
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }

    // 将15位身份证升级到18位
    public static function idcard_15to18($idcard){
        if (strlen($idcard) != 15){
            return false;
        }else{
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false){
                $idcard = substr($idcard, 0, 6) . '18'. substr($idcard, 6, 9);
            }else{
                $idcard = substr($idcard, 0, 6) . '19'. substr($idcard, 6, 9);
            }
        }
        $idcard = $idcard . self::idcard_verify_number($idcard);
        return $idcard;
    }

    // 18位身份证校验码有效性检查
    public static function idcard_checksum18($idcard){
        if (strlen($idcard) != 18){ return false; }
        $idcard_base = substr($idcard, 0, 17);
        if (self::idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))){
            return false;
        }else{
            return true;
        }
    }

    // 日志
    static function logResult($word='', $filename = 'log.txt', $log_type = '', $dir = '') {
        $log_dir = "/data/bak/log/";
        if( !empty($dir) )
            $log_dir = $log_dir . $dir;
        self::mkdirs($log_dir);
        $fp = fopen($log_dir . $filename,"a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time()).", {$log_type}\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    public static function writeLog($params,$file=''){
        $fp = fopen('/data/bak/log/gaoevip.txt','a');
        if(!$fp){
            return ;
        }

        $str = date('Y-m-d H:i:s') . ' ==> ';
        if(is_array($params)) {
            $str .= @var_export($params, true);
        } else {
            $str .= $params;
        }

        $str .= "\n";
        fwrite($fp, $str);
        fclose($fp);
    }

    // 创建目录，最高权限
    function mkdirs($dir) {
        if(!is_dir($dir)) {
            if(!mkdirs(dirname($dir))) {
                return false;
            }
            if(!mkdir($dir, 0777)){
                return false;
            }else{
                chmod($dir, 0777);
            }
        }
        return true;
    }

    /**
     * 自动验证
     * @param array $data [
     *                      [
     *                          验证数据,   必须 要验证的数据
     *                          验证规则,   必须 要进行验证的规则，需要联合附加规则一起使用
     *                                     (require（必须）、email（邮箱格式）、url（URL地址）、currency（货币）、number（数字）、mobile（手机号码）、english（英文字符）)
     *                          错误提示,   必须 当验证不通过时的提示信息
     *                          验证条件,   可选 1 ：必须验证 2 ：值不为空的时候验证
     *                          附加规则    可选 regex ：使用正则进行验证，表示前面定义的验证规则是一个正则表达式（默认）
     *                                         equal ：验证是否等于某个值，该值由前面的验证规则定义
     *                                         in ：验证是否在某个范围内，前面定义的验证规则必须是一个数组
     *                                         function ：使用函数验证，表示前面定义的验证规则是一个函数名
     *                      ]
     *                    ]
     * @return array
     */
    static public function autoValidation($data){
        if( empty($data) )
            return array(false, 'empty');

        $checked = true;
        foreach($data as $key => $val){
            switch($val[3]){
                case 2:    // 值不为空的时候才验证
                    if( !empty(trim($val[0])) )
                        $checked = self::validationField($val);
                    break;
                default: // 必须验证
                    $checked = self::validationField($val);
                    break;
            }
            if( $checked === false )
                return array(false, $val[2]);
        }
        return array(true, '');
    }

    static public function validationField($val){
        switch($val[4]){
            case 'function':
                if( is_array($val[0]) )
                    return @call_user_func_array($val[1], $val[0]);
                else
                    return @call_user_func($val[1], $val[0]);
                break;
            default:
                return self::check($val[0], $val[1], $val[4]);

        }
    }

    /**
     * 验证数据 支持 in between equal lt elt gt egt length regex expire ip_allow ip_deny
     * @access public
     * @param string $value 验证数据
     * @param mixed $rule 验证表达式
     * @param string $type 验证方式 默认为正则验证
     * @return boolean
     */
    static public function check($value, $rule, $type = 'regex'){
        $type = strtolower(trim($type));
        switch($type) {
            case 'in': // 验证是否在某个指定范围之内 逗号分隔字符串或者数组
            case 'notin':
                $range = is_array($rule) ? $rule : explode(',', $rule);
                $is_in = in_array($value, $range);
                return $type == 'in' ? $is_in : !$is_in;
            case 'between': // 验证是否在某个范围
            case 'notbetween': // 验证是否不在某个范围
                if( is_array($rule) ){
                    $min = $rule[0];
                    $max = $rule[1];
                }else{
                    list($min, $max) = explode(',', $rule);
                }
                return $type == 'between' ? $value >= $min && $value <= $max : $value < $min || $value > $max;
            case 'equal': // 验证是否等于某个值
            case 'notequal': // 验证是否等于某个值
                return $type == 'equal' ? $value == $rule : $value != $rule;
            case 'lt': // 验证是否小于某个值
            case 'gt': // 验证是否大于某个值
                return $type == 'lt' ? $value < $rule : $value > $rule;
            case 'elt': // 验证是否小于等于某个值
            case 'egt': // 验证是否大于等于某个值
                return $type == 'elt' ? $value <= $rule : $value >= $rule;
            case 'length': // 验证长度
                $length = mb_strlen($value, 'utf-8'); // 当前数据长度
                if( strpos($rule, ',') ){ // 长度区间
                    list($min, $max) = explode(',', $rule);
                    return $length >= $min && $length <= $max;
                }else{// 指定长度
                    return $length == $rule;
                }
            case 'expire':
                list($start, $end) = explode(',', $rule);
                if( !is_numeric($start) ) $start = strtotime($start);
                if( !is_numeric($end) ) $end = strtotime($end);
                return time() >= $start && time() <= $end;
            case 'ip_allow': // IP 操作许可验证
                return in_array(self::get_client_ip(), explode(',', $rule));
            case 'ip_deny': // IP 操作禁止验证
                return !in_array(self::get_client_ip(), explode(',', $rule));
            case 'regex':
            default:    // 默认使用正则验证 可以使用验证类中定义的验证名称
                // 检查附加规则
                return self::regex($value, $rule);
        }
    }

    /**
     * 使用正则验证数据
     * @access public
     * @param string $value  要验证的数据
     * @param string $rule 验证规则
     * @return boolean
     */
    static public function regex($value, $rule) {
        $validate = array(
            'require'   =>  '/.+/',
            'mobile'    => '/^(13[0-9]|15[0-9]|17[0-9]|18[0-9]|14[57])[0-9]{8}$/',
            'email'     =>  '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url'       =>  "/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/",
            'currency'  =>  '/^\d+(\.\d+)?$/',
            'number'    =>  '/^\d+$/',
            'zip'       =>  '/^\d{6}$/',
            'integer'   =>  '/^[-\+]?\d+$/',
            'double'    =>  '/^[-\+]?\d+(\.\d+)?$/',
            'english'   =>  '/^[A-Za-z]+$/',
            'chinese'   => '/^[\x{4e00}-\x{9fa5}]+$/u'
        );

        // 检查是否有内置的正则表达式
        if( isset($validate[strtolower($rule)]) )
            $rule = $validate[strtolower($rule)];

        return preg_match($rule, $value) === 1;
    }

    
    /*移动端判断*/
    static public function isMobileClient(){
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array (
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    // 获取开始结束时间
    static public function getStartEndTime($y, $m = 0, $w = 0){
        if( !empty($w) ){
            $w = $w < 10 ? '0' . $w : (string)$w;
            $start = strtotime($y . 'W' . $w);
            $end = strtotime('+1 week', $start) - 1;

            return array($start, $end);
        }

        if( !empty($m) ){
            $m = $m < 10 ? '0' . $m : (string)$m;
            $start = strtotime("$y-$m-01");
            $end = strtotime('+1 month', $start) - 1;

            return array($start, $end);
        }

        $start = strtotime("$y-01-01");
        $end = strtotime('+1 year', $start) - 1;

        return array($start, $end);
    }

}

//数组构造更新sql
function db_array_to_update_sqladd($arr) {
    $s = '';
    foreach($arr as $k=>$v) {
        $v = addslashes($v);
        $op = substr($k, -1);
        if($op == '+' || $op == '-') {
            $k = substr($k, 0, -1);
            $v = (is_int($v) || is_float($v)) ? $v : "'$v'";
            $s .= "`$k`=$k$op$v,";
        } else {
            $v = (is_int($v) || is_float($v)) ? $v : "'$v'";
            $s .= "`$k`=$v,";
        }
    }
    return substr($s, 0, -1);
}

//数组构造新增sql
function db_array_to_insert_sqladd($arr) {
    $s = '';
    $keys = array();
    $values = array();
    foreach($arr as $k=>$v) {
        $k = addslashes($k);
        $v = addslashes($v);
        $keys[] = '`'.$k.'`';
        $v = (is_int($v) || is_float($v)) ? $v : "'$v'";
        $values[] = $v;
    }
    $keystr = implode(',', $keys);
    $valstr = implode(',', $values);
    $sqladd = "($keystr) VALUES ($valstr)";
    return $sqladd;
}


function db_orderby_to_sqladd($orderby) {
    $s = '';
    if(!empty($orderby)) {
        $s .= ' ORDER BY ';
        $comma = '';
        foreach($orderby as $k=>$v) {
            $s .= $comma."`$k` ".($v == 1 ? ' ASC ' : ' DESC ');
            $comma = ',';
        }
    }
    return $s;
}

function db_cond_to_sqladd($cond) {
    $s = '';
    if(!empty($cond)) {
        $s = ' WHERE ';
        foreach($cond as $k=>$v) {
            if(!is_array($v)) {
                $v = (is_int($v) || is_float($v)) ? $v : "'".addslashes($v)."'";
                $s .= "`$k`=$v AND ";
            } elseif(isset($v[0])) {
                // OR 效率比 IN 高
                $s .= '(';
                //$v = array_reverse($v);
                foreach ($v as $v1) {
                    $v1 = (is_int($v1) || is_float($v1)) ? $v1 : "'".addslashes($v1)."'";
                    $s .= "`$k`=$v1 OR ";
                }
                $s = substr($s, 0, -4);
                $s .= ') AND ';

                /*
                $ids = implode(',', $v);
                $s .= "$k IN ($ids) AND ";
                */
            } else {
                foreach($v as $k1=>$v1) {
                    if($k1 == 'LIKE') {
                        $k1 = ' LIKE ';
                        $v1="%$v1%";
                    }
                    $v1 = (is_int($v1) || is_float($v1)) ? $v1 : "'".addslashes($v1)."'";
                    $s .= "`$k`$k1$v1 AND ";
                }
            }
        }
        $s = substr($s, 0, -4);
    }
    return $s;
}

//获取变量名称
/**
 * @param String $var 要查找的变量
 * @param Array $scope 要搜寻的范围
 * @param String 变量名称
 */
function get_variable_name(&$var, $scope=null){
    $scope = $scope==null? $GLOBALS : $scope; // 如果没有范围则在globals中找寻
    // 因有可能有相同值的变量,因此先将当前变量的值保存到一个临时变量中,然后再对原变量赋唯一值,以便查找出变量的名称,找到名字后,将临时变量的值重新赋值到原变量
    $tmp = $var;

    $var = 'tmp_value_'.mt_rand();
    $name = array_search($var, $scope, true); // 根据值查找变量名称

    $var = $tmp;
    return $name;
}

//读取config文件
function getconfig( $ini, $type="string")
{
    $file=CONF;
    if ($type=="int")
    {
        $str = file_get_contents($file);
        $config = preg_match("/" . $ini . "=(.*);/", $str, $res);
        Return $res[1];
        //return _len($res[1]);
    }
    else
    {
        $str = file_get_contents($file);
        $config = preg_match("/" . $ini . "=\"(.*)\";/", $str, $res);
        if($res[1]==null)
        {
            $config = preg_match("/" . $ini . "='(.*)';/", $str, $res);
        }
        Return $res[1];
        //return _len($res[1]);
    }
}

//修改config.php文件
function setconfig($ini, $value,$type="string"){
    $file=CONF;
    $str = file_get_contents($file);
    $str2="";
    if(preg_match("/".$ini."=(.*);/",$str)){
        if($type=="int"){
            $str2 = preg_replace("/".$ini."=(.*);/", $ini."=".$value.";", $str);
        }else{
            $str2 = preg_replace("/".$ini."=(.*);/", $ini."=\"".$value."\";",$str);
        }
    }else{
        $str2=$str."\n".$ini."=".$value;
    }

    file_put_contents($file, $str2);
}

function arr2s($db,$array, $type='insert', $exclude = array()){
    $sql = '';
    if(count($array) > 0){
        foreach ($exclude as $exkey) {
            unset($array[$exkey]);//剔除不要的key
        }

        if('insert' == $type){
            $keys = array_keys($array);
            $values = array_values($array);
            $col = implode("`, `", $keys);
            $val = implode("', '", $values);
            $sql = "(`$col`) values('$val')";
        }else if('update' == $type){
            $tempsql = '';
            $temparr = array();
            foreach ($array as $key => $value) {
                $tempsql = "'$key' = '$value'";
                $temparr[] = $tempsql;
            }

            $sql = implode(",", $temparr);
        }
    }
    $sql="insert into `{$db}` {$sql}";
    //mysql_query($sql,$this->_db)
    return $sql;
}
//生成ac参数
function goac($a,$c){
    $url= '?a='.$a.'&c='.$c;
    echo  $url;
}
//获取ac参数
function getac(){
    $a=get("a");
    $c=get("c");
    return '?a='.$a.'&c='.$c;
}
//获取am参数
function getam(){
    $a=get("a");
    $m=get("m");
    return '?a='.$a.'&m='.$m;
}
//获取地址当前参数
function getcs(){
    $res=parse_url(geturl());
    $query='?'.$res['query'];
    return $query;
}

//获取当前url
function geturl(){
    return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}