<?php
/**
 * Name: class.wi_httpclient.php
 * Description: ����Snoopy(ʹ��PHP��չCurl���������Curl)��PHP������ͻ���ģ����
 * Created by: Chenjin(wind.golden@gmail.com)
 * Project : wind_frame
 * Time: 2010-6-1
 * Version: 1.0
 */
require (__DIR__ . '/Snoopy/SnoopyCurl.class.php');
class WI_HttpClient extends SnoopyCurl {
    function WI_HttpClient() {
        if (isset(WI_CONFIG::$httpclient)) {
            $this->proxy_host = WI_CONFIG::$httpclient['proxy_host'];
            $this->proxy_port = WI_CONFIG::$httpclient['proxy_port'];
            $this->proxy_user = WI_CONFIG::$httpclient['proxy_user'];
            $this->proxy_pass = WI_CONFIG::$httpclient['proxy_pass'];
            $this->agent = WI_CONFIG::$httpclient['agent'];
            $this->read_timeout = WI_CONFIG::$httpclient['read_timeout'];
            $this->_fp_timeout = WI_CONFIG::$httpclient['exec_timeout'];
        }
        else {
            $this->agent = 'HttpClient 1.0';
            $this->read_timeout = 30;
            $this->_fp_timeout = 30;
            //trigger_error('HttpClient: use default config',E_USER_NOTICE);
            
        }
    }
}
