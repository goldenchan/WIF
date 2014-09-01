<?php
/**
 *  email发送处理类文件
 *
 * @filename   : cached_template.php
 * @description : 缓存模板处理类
 * @package helper
 * @author: Chenjin (wind.golden@gmail.com)
 */
require __DIR__ . DS . 'baselib' . DS . 'Mailer' . DS . 'class.phpmailer.php';
/**
 * email 处理类
 */
class Email {
    /** 
     * 默认发送邮件需要smtp验证
     * @var string
     */
    var $from = '';
    /**
     * 源邮件的发送人
     * @var string
     */
    var $fromName = '';
    /**
     *  smtp连接用户名
     *  @var string
     */
    var $smtpUserName = '';
    /**
     * smtp服务器连接密码
     * @var string
     */
    var $smtpPassword = '';
    /**
     * 指定主服务器和备份服务器
     * @var string
     */
    var $smtpHostNames = '';
    //var $smtpSecure	= 'ssl';
    
    /**
     * 端口号
     * @var integer
     */
    var $port = 25;
    /**
     * 邮件正文 text
     * @var string
     */
    var $text_body = null;
    /**
     * 邮件正文html
     * @var string
     */
    var $html_body = null;
    /**
     * 接受人邮件
     * @var string
     */
    var $to = null;
    /**
     * 接受人姓名
     * @var string
     */
    var $toName = null;
    /**
     * 标题
     * @var string
     */
    var $subject = null;
    /**
     * view 对象
     * @var object
     */
    var $view = null; //smarty 渲染句柄
    
    /**
     * 默认模版
     * @var string
     */
    var $template = 'default'; //默认发送邮件套用模板
    
    /**
     * 附件
     *@var array
     */
    var $attachments = null;
    /**
     * 错误信息
     * @var string
     */
    var $error_info = '';
    /**
     * action 函数？
     */
    var $action_function = null;
    /**
     * 抄送人邮箱
     * @var string
     */
    var $cc1;
    /**
     * 密送人邮箱
     * @var string
     */
    var $bcc1;
    /**
     * 抄送人名
     * @var string
     */
    var $cc1Name = '';
    /**
     * 密送人名
     * @var string
     */
    var $bcc1Name = '';
    /**
     * 是否html
     * @var boolean
     */
    var $is_html = true;
    /**
     * 构造函数
     *  @param string $from  发送邮箱
     *  @param string $from_name 发送人名字
     *  @param string $smtpUserName smtp 用户名
     *  @param string $smtpPassword smtp password
     *  @param string $smtpHostNames host
     *  @param int $port 端口
     */
    function __construct($from = '', $from_name = '', $smtpUserName = '', $smtpPassword = '', $smtpHostNames = '', $port = '') {
        $this->from = $from;
        $this->fromName = $from_name;
        $this->smtpUserName = $smtpUserName;
        $this->smtpPassword = $smtpPassword;
        $this->smtpHostNames = $smtpHostNames;
        $this->port = $port;
    }
    /** 
     * 纯文本方式邮件发送
     * @return string text content
     */
    function bodyText() {
        if (null === $this->text_body) {
            $mail = $this->view->fetch();
        }
        else {
            $mail = $this->text_body;
        }
        return $mail;
    }
    /** 
     * html方式邮件发送
     * @return string html content
     */
    function bodyHTML() {
        if (null === $this->text_body) {
            $mail = $this->view->fetch();
        }
        else {
            $mail = $this->html_body;
        }
        return $mail;
    }
    /** 
     * 添加附件
     * @param string $filename 附件文件名
     * @param string $asfile 附件路径
     */
    function attach($filename, $asfile = '') {
        if (empty($this->attachments)) {
            $this->attachments = array();
            $this->attachments[0]['filename'] = $filename;
            $this->attachments[0]['asfile'] = $asfile;
        }
        else {
            $count = count($this->attachments);
            $this->attachments[$count + 1]['filename'] = $filename;
            $this->attachments[$count + 1]['asfile'] = $asfile;
        }
    }
    /**
     * 发送邮件
     * @return true or false
     *
     */
    function send() {
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = false;
            $mail->IsSMTP(); // set mailer to use SMTP
            $mail->SMTPAuth = true; // turn on SMTP authentication
            $mail->Host = $this->smtpHostNames;
            $mail->Port = $this->port;
            $mail->Username = $this->smtpUserName;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = $this->smtpSecure;
            $mail->SetFrom($this->from, $this->fromName);
            $mail->AddAddress($this->to, $this->toName);
            if (strlen($this->cc1) > 5) {
                $mail->AddCC($this->cc1, $this->cc1Name);
            }
            if (strlen($this->bcc1) > 5) {
                $mail->AddBCC($this->bcc1, $this->bcc1Name);
            }
            $mail->AddReplyTo($this->from, $this->fromName);
            $mail->CharSet = 'UTF-8';
            $mail->SetLanguage('zh_cn');
            $mail->WordWrap = 50; // set word wrap to 50 characters
            if (!empty($this->attachments)) {
                foreach ($this->attachments as $attachment) {
                    if (empty($attachment['asfile'])) {
                        $mail->AddAttachment($attachment['filename']);
                    }
                    else {
                        $mail->AddAttachment($attachment['filename'], $attachment['asfile']);
                    }
                }
            }
            $mail->Subject = $this->subject;
            $mail->MsgHTML($this->bodyHTML());
            $mail->IsHTML($this->is_html);
            $mail->action_function = $this->action_function;
            $mail->Send();
            return true;
        }
        catch(phpmailerException $e) {
            $this->error_info = $e->errorMessage(); //Pretty error messages from PHPMailer
            return false;
        }
        catch(Exception $e) {
            $this->error_info = $e->getMessage(); //Boring error messages from anything else!
            return false;
        }
    }
}
