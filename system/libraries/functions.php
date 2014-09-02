<?php
/**
 * 全局调用函数
 * @file functions.php
 * @author 陈金(wind.golden@gmail.com)
 */
/**
 *  Get file content
 * @param string $file_name  文件路径
 * @param integer $read_size 读取的大小
 * @return string file content
 */
function read_file($file_name, $read_size = 0) {
    if ($read_size > 0) {
        if (!@$fp = fopen($file_name, 'rb')) {
            return false;
        }
        flock($fp, LOCK_SH);
        $content = @fread($fp, $read_size);
        flock($fp, LOCK_UN); //释放共享锁
        fclose($fp);
        return $content;
    }
    elseif (function_exists('file_get_contents')) {
        return @file_get_contents($file_name);
    }
}
/**
 *  write string into the  file
 * @param string $file_name file path
 * @param string $content content which will be written into file
 * @param int $mode file mode
 * @return boolean true if sucess else return false
 */
function write_file($file_name, $content = '', $mode = 0777) {
    if (file_exists($file_name)) {
        @chmod($file_name, $mode);
    }
    if (function_exists('file_put_contents')) {
        $status = @file_put_contents($file_name, $content);
        @chmod($file_name, $mode);
        return $status;
    }
    else {
        if (!@$fp = fopen($file_name, 'wb')) {
            return false;
        }
        @chmod($file_name, $mode);
        flock($fp, LOCK_EX);
        @fwrite($fp, $content);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }
}
/**
 *  appden string into the  file
 * @param string $file_name file path
 * @param string $content which will be written into file
 * @return boolean true if sucess else return false
 */
function append_file($file_name, $content) {
    // Append if the fila already exists...
    if (file_exists($file_name)) {
        file_put_contents($file_name, $content, FILE_APPEND);
        // Note: use LOCK_EX if an exclusive lock is needed.
        // file_put_contents($file,  $data, FILE_APPEND | LOCK_EX);
        
    }
    // Otherwise write a new file...
    else {
        write_file($file_name, $content);
    }
}
/**
 *  create directory
 * @param string $dir_name directory name(include path)
 * @param int $mode directory mode
 * @return boolean true if sucess else return false
 */
function create_dir($dir_name, $mode = 0774) {
    $dir_name = str_replace('\\', '/', $dir_name);
    //$dir_name = realpath($dir_name);
    $parts = explode('/', trim($dir_name));
    $pre = substr($dir_name, 0, 1);
    $tested = ('/' == $pre) ? '/' : '';
    for ($i = 0; $i < count($parts); $i++) {
        $curr = $parts[$i];
        if (isset($curr)) {
            $tested.= $curr . '/';
            if (!is_dir($tested)) {
                if (!@mkdir($tested, $mode)) {
                    return false;
                }
                @chmod($tested, $mode);
            }
        }
    }
    return $tested;
}
/**
 *  delete directory
 * @param string $dir_name 目录名
 * @param boolean $recursive whether recursive delete
 * @param boolean $only_files whether only delete files and leave the directory
 * @param array $excluded_files  all the exclued_files
 * @return boolean true
 */
function delete_dir($dir_name, $recursive = false, $only_files = false, $excluded_files = array(
    ''
)) {
    if (!file_exists($dir_name) || !is_readable($dir_name)) {
        return false;
    }
    if (!is_dir($dir_name)) {
        return @unlink($dir_name);
    }
    $file_name = $dir_name;
    if (substr($file_name, -1) != "/") $file_name.= "/";
    $file_name.= "*";
    $files = glob($file_name, 0);
    foreach ($files as $file) {
        // check if the filename is in the list of files we must not delete
        if (is_dir($file) && array_search(basename($file) , $excluded_files) === false) {
            // perform a recursive call if we were allowed to do so
            if ($recursive) {
                delete_dir($file, $recursive, $only_files, $excluded_files);
            }
        }
        // check if the filename is in the list of files we must not delete
        if (array_search(basename($file) , $excluded_files) === false) {
            // File::delete can remove empty folders as well as files
            if (is_readable($file) && !is_dir($file)) {
                @unlink($file);
            }
        }
    }
    // finally, remove the top-level folder but only in case we
    // are supposed to!
    if (!$only_files) {
        @rmdir($dir_name);
    }
    return true;
}
/**
 * Returns an underscore-syntaxed ($like_this_dear_reader) version of the $camel_cased_word.
 *
 * @param string $camel_cased_word Camel-cased word to be "underscorized"
 * @return string Underscore-syntaxed version of the $camel_cased_word
 * @access public
 * @static
 */
function word_underscore($camel_cased_word) {
    return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camel_cased_word));
}
/**
 * Returns an camelcased-syntaxed ($LikeThisDearReader) version .
 *
 * @param string $underscore_word Camel-cased word to be "underscorized"
 * @return string Underscore-syntaxed version of the $camel_cased_word
 * @access public
 * @static
 */
function word_camelcase($underscore_word) {
    if (strpos($underscore_word, "_") > 0) {
        return implode(array_map('ucfirst', explode('_', $underscore_word)));
    }
    else return ucfirst($underscore_word);
}
/**
 * Recursively strips slashes from all values in an array
 *
 * @param array $value Array of values to strip slashes
 * @return mixed What is returned from calling stripslashes
 */
function stripslashes_deep($value) {
    if (is_array($value)) {
        $return = array_map('stripslashes_deep', $value);
        return $return;
    }
    else {
        $return = stripslashes($value);
        return $return;
    }
}
/**
 * spl_register_autoload用的函数
 * @param $class 类名
 */
function auto_load($class) {
    //include strtolower($class).'.php';
    spl_autoload(strtolower($class));
}
/**
 * 根据类名和组包含文件
 * @param mixed $class_names 类名
 * @param mixed $class_group 类所在组
 * @param boolean $return_obj 是否返回对象,
 * @return mixed  object or boolean
 */
function load_class($class_names = '', $class_group = '', $return_obj = false) {
    if (is_string($class_names) && $class_names !== '') {
        $file = APP_ROOT_PATH . $class_group . DS . word_underscore($class_names) . ".php";
        require $file;
        if ($return_obj === true) {
            $tmp = word_camelcase($class_names);
            return new $tmp;
        }
    }
    else if (is_array($class_names) && count($class_names) > 0) {
        $object = array();
        foreach ($class_names as $class_name) {
            $file = APP_ROOT_PATH . $class_group . DS . word_underscore($class_name) . ".php";
            require $file;
            if ($return_obj === true) {
                $object[] = new word_camelcase($class_name);
            }
        }
        return ($return_obj === true ? $object : true);
    }
    return true;
}
/**
 * 加载配置项
 * @param string $config_file configs目录下的文件名 必填项
 * @param string $section 配置setion 可选项
 */
function load_config($config_file, $section = null) {
    include APP_ROOT_PATH . 'configs' . DS . $config_file . '.php';
    if ($section !== null) return $config[$section];
    else return $config;
}
/**
 * 获取随机Hash串
 * @param integer $length string length
 * @param boolean $extra 是否包含特殊字符
 * @return string  the hash string
 */
function get_random_str($length = 10, $extra = false) {
    $hash = '';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
    $extra_chars = '~!@#$%^&*()_+{}:`[]=-'; //不包含 "|" , "?" , "<" , ">", 单引号和双引号
    $extra && $chars.= $extra_chars;
    $max = strlen($chars) - 1;
    mt_srand((double)microtime() * 1000000);
    for ($i = 0; $i < $length; $i++) {
        $hash.= $chars[mt_rand(0, $max) ];
    }
    return $hash;
}
/**
 * 获取随机数
 * @param integer $length number length
 * @return string  随机数
 */
function get_random_num($length = 5) {
    $hash = '';
    $chars = '0123456789';
    $max = strlen($chars) - 1;
    mt_srand((double)microtime() * 1000000);
    for ($i = 0; $i < $length; $i++) {
        $hash.= $chars[mt_rand(0, $max) ];
    }
    return $hash;
}
/**
 * Gets remote client IP
 *
 * @return string Client IP address
 * @access public
 */
function get_client_ip() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != null) {
        $ipaddr = preg_replace('/,.*/', '', $_SERVER['HTTP_X_FORWARDED_FOR']);
    }
    else {
        if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != null) {
            $ipaddr = $_SERVER['HTTP_CLIENT_IP'];
        }
        else {
            $ipaddr = $_SERVER['REMOTE_ADDR'];
        }
    }
    if (isset($_SERVER['HTTP_CLIENTADDRESS']) && $_SERVER['HTTP_CLIENTADDRESS'] != null) {
        $tmpipaddr = $_SERVER['HTTP_CLIENTADDRESS'];
        if (!empty($tmpipaddr)) {
            $ipaddr = preg_replace('/,.*/', '', $tmpipaddr);
        }
    }
    return trim($ipaddr);
}
/**
 *  获取当前的url地址
 */
function current_url() {
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $pageURL.= "s";
    }
    $pageURL.= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL.= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    }
    else {
        $pageURL.= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}
/**
 * 初始化session设置
 * @param $expire_seconds 默认过期时间
 */
function init_session($expire_seconds = 180000) {
    if (!session_id()) {
        ini_set('session.cookie_lifetime', '0');
        ini_set('session.gc_maxlifetime', strval(7 * 24 * 3600));
        //ini_set('session.gc_probability','0');
        session_cache_limiter("nocache");
        $sessionName = "WI" . str_replace('.', '', SITE_DOMAIN);
        session_name($sessionName);
        session_set_cookie_params(strval($expire_seconds));
        session_start();
        if (!isset($_SESSION['WI_token'])) {
            $_SESSION['WI_token'] = md5(uniqid(rand() , true));
        }
    }
}
/**
 * 清空session
 */
function destroy_all_sessions() {
    $sessionName = $sessionName = "WI" . str_replace('.', '', SITE_DOMAIN);;
    setcookie($sessionName, "", time() - 3600);
    session_destroy();
}
/**
 *  添加外部类
 * @param $string 外部类相对路径
 */
function vendor($string) {
    include_once APP_ROOT_PATH . 'vendor' . DS . $string;
}
/**
 *  调用远程http接口，返回接口执行的结果
 * @param string $uri 接口地址
 * @param array $option 选项设置 （POST过来的参数）
 * @param string $method 请求方式 GET or POST
 * @param mixed 返回结果
 */
function request_http_client($uri = '', $option = array() , $method = 'GET') {
    if ($uri === '') return 'true';
    require_once __DIR__ . DS . 'baselib' . DS . 'class.wi_httpclient.php';
    $http_client = new WI_HttpClient();
    if (strtoupper($method) == 'POST') {
        if (!empty($option['formfiles'])) {
            $http_client->set_submit_multipart();
        }
        //$decodefile = $option['formvars']['file_name'];
        //$option['formvars']['file_name'] = urldecode($decodefile);
        $http_client->submit($uri, $option['formvars'], $option['formfiles']);
        $results = $http_client->results;
    }
    else if (strtoupper($method) == 'GET') {
        $http_client->fetch($uri);
        $results = $http_client->results;
    }
    else {
        $results = false;
    }
    return $results;
}
/** 
 * 计算身份证校验码，根据国家标准GB 11643-1999
 * @param string $idcard_base 身份证号前17位
 * @return string 根据前17位生成最后一位校验位
 */
function idcard_verify_number($idcard_base) {
    if (mb_strlen($idcard_base) !== 17) {
        return false;
    }
    $factor = array(
        7,
        9,
        10,
        5,
        8,
        4,
        2,
        1,
        6,
        3,
        7,
        9,
        10,
        5,
        8,
        4,
        2
    );
    $verify_number_list = array(
        '1',
        '0',
        'X',
        '9',
        '8',
        '7',
        '6',
        '5',
        '4',
        '3',
        '2'
    );
    $checksum = 0;
    for ($i = 0; $i < 17; $i++) {
        $checksum+= $idcard_base{$i} * $factor[$i];
    }
    return $verify_number_list[$checksum % 11];
}
/**
 *  15或18位身份证校验码有效性检查
 * @param string $idcard 身份证号
 * @return true if valid  else return false
 */
function idcard_checksum($idcard) {
    if (strlen($idcard) != 18 && strlen($idcard) != 15) {
        return false;
    }
    if (strlen($idcard) == 15) {
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (array_search(substr($idcard, 12, 3) , array(
            '996',
            '997',
            '998',
            '999'
        )) !== false) {
            $idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 9);
        }
        else {
            $idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 9);
        }
        $idcard = $idcard . idcard_verify_number($idcard);
    }
    $idcard_base = substr($idcard, 0, 17);
    return (idcard_verify_number($idcard_base) == strtoupper($idcard{17}));
}
/** 
 * 带salt的加密
 * @param string $text 要加密的串
 * @param $salt hash_key
 * @return string 加密后的串
 */
function encrypt($text, $salt) {
    return trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB) , MCRYPT_RAND))));
}
/**
 * 带salt的解密
 * @param string $text 要解密的串
 * @param $salt hash_key
 * @return string 解密后的串
 */
function decrypt($text, $salt) {
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, hex2bin($text) , MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB) , MCRYPT_RAND)));
}
/**
 * Sanitize Filename
 *
 * @param   string  $str        Input file name
 * @param   bool    $relative_path  Whether to preserve paths
 * @return  string
 */
function sanitize_filename($str, $relative_path = FALSE) {
    $bad = array(
        '../',
        '<!--',
        '-->',
        '<',
        '>',
        ' ',
        "'",
        '"',
        '&',
        '$',
        '#',
        '{',
        '}',
        '[',
        ']',
        '=',
        ';',
        '?',
        '%20',
        '%22',
        '%3c', // <
        '%253c', // <
        '%3e', // >
        '%0e', // >
        '%28', // (
        '%29', // )
        '%2528', // (
        '%26', // &
        '%24', // $
        '%3f', // ?
        '%3b', // ;
        '%3d'
        // =
        
    );
    if (!$relative_path) {
        $bad[] = './';
        $bad[] = '/';
    }
    $str = remove_invisible_characters($str, FALSE);
    return time() . stripslashes(str_replace($bad, '', $str));
}
/**
 * remove invisible characters
 * @param string $str 字符串
 * @param boolean $url_encoded 是否做urlencode编码
 * @return string 处理后的字符串
 */
function remove_invisible_characters($str, $url_encoded = TRUE) {
    $non_displayables = array();
    // every control character except newline (dec 10),
    // carriage return (dec 13) and horizontal tab (dec 09)
    if ($url_encoded) {
        $non_displayables[] = '/%0[0-8bcef]/'; // url encoded 00-08, 11, 12, 14, 15
        $non_displayables[] = '/%1[0-9a-f]/'; // url encoded 16-31
        
    }
    $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'; // 00-08, 11, 12, 14-31, 127
    do {
        $str = preg_replace($non_displayables, '', $str, -1, $count);
    } while ($count);
    return $str;
}
/** 
 * 获取文件类型mime_type
 * @param string $filename 文件路径名
 * @param string $ext 文件扩展名
 * @return $string 文件mime_type
 */
function get_mime_type($filename, $ext = '') {
    $output = array();
    $returncode = 0;
    $mime_type = exec('file -b --mime-type "' . $filename . '"', $output, $returncode); //需要 类Linux 环境
    if ('CDF V2 Document, No summary info' === $mime_type || 'application/zip' === $mime_type) {
        $mime_type = 'application/vnd.ms-office';
    }
    $mime_types = array(
        'ppt' => 'application/vnd.ms-powerpoint',
        'xls' => 'application/vnd.ms-excel',
        'doc' => 'application/vnd.ms-word',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    );
    if (in_array($ext, array_keys($mime_types)) && (in_array($mime_type, array(
        'application/msword',
        'application/vnd.ms-office'
    )) || (substr($ext, -1) === 'x' && $mime_type === $mime_types[substr($ext, 0, -1) ]))) {
        $mime_type = $mime_types[$ext];
    }
    return $mime_type;
}
/**
 *   中文转unicode
 * @example'<style>font:"微软正黑体"</style>' 转成 '<STYLE>FONT:"\5FAE\8F6F\6B63\9ED1\4F53"</STYLE>'
 * @param string $name 要转换的字符串
 * @param string $charset 要转换字符串的编码 默认utf-8
 * @return $string 转换后的字符串
 */
function unicode_encode($name, $charset = 'UTF-8') { //to Unicode
    $name = iconv($charset, 'UCS-2', $name);
    $len = strlen($name);
    $str = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2) {
        $c = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0) { // 两个字节的字
            $str.= '\\' . base_convert(ord($c) , 10, 16) . base_convert(ord($c2) , 10, 16);
        }
        else {
            $str.= $c2;
        }
    }
    $str = strtoupper($str);
    return $str;
}
/**
 * 获取对象的public 属性
 * @param object $obj 被操作的对象
 * @return array 所有public属性
 */
function my_get_object_vars($obj) {
    $ref = new ReflectionObject($obj);
    $pros = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
    $result = array();
    foreach ($pros as $pro) {
        false && $pro = new ReflectionProperty();
        $result[$pro->getName() ] = $pro->getValue($obj);
    }
    return $result;
}
/**
 * 设置未申明的变量为默认值
 *
 * @param $var 变量
 * @param $default 值
 */
function default_value(&$var, $default) {
    if (!isset($var)) {
        $var = $default;
    }
}
