<?php
/**
 * error 处理 存到文件
 * @file error_handler.php
 * @package bootstrap
 * @author 陈金(wind.golden@gmail.com)
 */
/**
 * error 处理类
 */
class Error_Handler {
    /**
     * 构造器
     */
    function __construct() {
        register_shutdown_function(array($this,
            'shutdownError'
        ));
    }
    /**
     * 处理非fatal错误 runtime 执行
     */
    public function runtimeError() {
        // if error has been supressed with an @
        if (error_reporting() === 0) {
            return;
        }
        // check if function has been called by an exception
        if (func_num_args() == 5) {
            // called by trigger_error()
            $exception = null;
            list($errno, $errstr, $errfile, $errline) = func_get_args();
            $backtrace = array_reverse(debug_backtrace());
        }
        else {
            // caught exception
            $exc = func_get_arg(0);
            $errno = $exc->getCode();
            $errstr = $exc->getMessage();
            $errfile = $exc->getFile();
            $errline = $exc->getLine();
            $backtrace = $exc->getTrace();
        }
        $errorType = array(
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSING ERROR',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE ERROR',
            E_CORE_WARNING => 'CORE WARNING',
            E_COMPILE_ERROR => 'COMPILE ERROR',
            E_COMPILE_WARNING => 'COMPILE WARNING',
            E_USER_ERROR => 'USER ERROR',
            E_USER_WARNING => 'USER WARNING',
            E_USER_NOTICE => 'USER NOTICE',
            E_STRICT => 'STRICT NOTICE',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR'
        );
        // create error message
        if (array_key_exists($errno, $errorType)) {
            $err = $errorType[$errno];
        }
        else {
            $err = 'CAUGHT EXCEPTION';
        }
        $errMsg = "$err: $errstr in $errfile on line $errline\n";
        // start backtrace
        foreach ($backtrace as $v) {
            if (isset($v['class'])) {
                $trace = 'in class ' . $v['class'] . '::' . $v['function'] . "(";
                if (isset($v['args'])) {
                    $separator = '';
                    foreach ($v['args'] as $arg) {
                        $trace.= "$separator" . $this->getArgument($arg);
                        $separator = ",\n";
                    }
                }
                $trace.= ")";
            }
            elseif (isset($v['function']) && empty($trace)) {
                $trace = 'in function ' . $v['function'] . "(";
                if (!empty($v['args'])) {
                    $separator = '';
                    foreach ($v['args'] as $arg) {
                        $trace.= "$separator" . $this->getArgument($arg);
                        $separator = ",\n";
                    }
                }
                $trace.= ")";
            }
        }
        // display error msg, if debug is enabled
        if (ini_get('display_errors') === "1") {
            echo '<h2>Debug Message For Error </h2>' . nl2br($errMsg) . '<br />
				 Trace: ' . nl2br($trace) . '<br />';
        }
        else {
            // send email to admin and log
            $error = 'Debug Message For Error ' . $errMsg . ';Trace: ' . $trace;
            error_log($error);
        }
        // what to do
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                break;

            default:
                exit($this->displayClientMessage());
                break;
        }
    } // end of errorHandler()
    
    /**
     * 显示到客户端
     */
    function displayClientMessage() {
        echo '<b>Server Error, Please Try Again Later！</b>';
    }
    /**
     * 获取参数
     * @param $arg 参数
     */
    function getArgument($arg) {
        switch (strtolower(gettype($arg))) {
            case 'string':
                return ('"' . str_replace(array(
                    "\n"
                ) , array(
                    ''
                ) , $arg) . '"');
            case 'boolean':
                return (bool)$arg;
            case 'object':
                return 'object(' . get_class($arg) . ')';
            case 'array':
                $ret = 'array(';
                $separtor = '';
                foreach ($arg as $k => $v) {
                    $ret.= $separtor . $this->getArgument($k) . ' => ' . $this->getArgument($v);
                    $separtor = ', ';
                }
                $ret.= ')';
                return $ret;
            case 'resource':
                return 'resource(' . get_resource_type($arg) . ')';
            default:
                return var_export($arg, true);
        }
    }
    /**
     * 处理fatar error 在php shutdown 后执行
     */
    function shutdownError() {
        $errfile = "unknown file";
        $errstr = "shutdown";
        $errno = E_CORE_ERROR;
        $errline = 0;
        $error = error_get_last();
        if ($error !== NULL) {
            $errno = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr = $error["message"];
            //if (ini_get('display_errors') === "1") {
                echo $this->format_error($errno, $errstr, $errfile, $errline);
            //}
            //发送到邮箱或日志
            
        }
    }
    /**
     * 格式化错误
     * @param $errno 错误码
     * @param $errstr 错误串
     * @param $errfile 错误文件
     * @param $errline 错误行
     * @return string 格式化后的错误信息
     */
    function format_error($errno, $errstr, $errfile, $errline) {
        $trace = print_r(debug_backtrace(false) , true);
        $content = "<table><thead bgcolor='#c8c8c8'><th>Item</th><th>Description</th></thead><tbody>";
        $content.= "<tr valign='top'><td><b>Error</b></td><td><pre>$errstr</pre></td></tr>";
        $content.= "<tr valign='top'><td><b>Errno</b></td><td><pre>$errno</pre></td></tr>";
        $content.= "<tr valign='top'><td><b>File</b></td><td>$errfile</td></tr>";
        $content.= "<tr valign='top'><td><b>Line</b></td><td>$errline</td></tr>";
        $content.= "<tr valign='top'><td><b>Trace</b></td><td><pre>$trace</pre></td></tr>";
        $content.= '</tbody></table>';
        return $content;
    }
}
