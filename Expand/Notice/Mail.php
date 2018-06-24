<?php
/**
 * PESCMS for PHP 5.4+
 *
 * Copyright (c) 2015 PESCMS (http://www.pescms.com)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 * @core version 2.6
 * @version 1.0
 */

namespace Expand\Notice;

class Mail {

    /**
     * 暴露一个PHPMailer的对象
     * @var \PHPMailer
     */
    public $PHPMailer;

    public function __construct() {
        //读取邮件账号信息
        $mail = json_decode(\Model\Content::findContent('option', 'mail', 'option_name')['value'], true);

        require_once dirname(__FILE__) . '/PHPMailerAutoload.php';
        $this->PHPMailer = new \PHPMailer;
        $this->PHPMailer->CharSet = "utf-8";
        $this->PHPMailer->isSMTP();
        $this->PHPMailer->Debugoutput = 'html';
        $this->PHPMailer->Host = $mail['address'];
        $this->PHPMailer->SMTPAuth = true;
        $this->PHPMailer->Username = $mail['account'];
        $this->PHPMailer->Password = $mail['passwd'];
	    if($mail['port'] != '25'){
		    $this->PHPMailer->SMTPSecure = 'tls';
	    }
	    $this->PHPMailer->FromName =  empty($mail['formname']) ? 'system' : $mail['formname'];
        $this->PHPMailer->Port = $mail['port'];
        $this->PHPMailer->From = $mail['account'];
    }

    /**
     * 发送邮件
     */
    public function send() {
        foreach(\Model\Content::listContent(['table' => 'send', 'condition' => 'send_type = 1 AND send_time = 0 ']) as $value){

            if(\Model\Extra::checkInputValueType($value['send_account'], 1) === false){
                return false;
            }

            $this->PHPMailer->addAddress($value['send_account']);

            $this->PHPMailer->WordWrap = 50;
            $this->PHPMailer->isHTML(true);

            $this->PHPMailer->Subject = $value['sned_title'];
            $this->PHPMailer->Body = $value['send_content'];

            if ($this->PHPMailer->send() !== false) {
                //发送成功，那么它就没有存在的价值了！干掉它！
                \Core\Func\CoreFunc::db('send')->where('send_id = :send_id')->delete([
                    'send_id' => $value['send_id']
                ]);
            }
            $this->PHPMailer->ClearAddresses();
        }

    }

	/**
	 * 邮件发送测试
	 * @throws \Exception
	 * @throws \phpmailerException
	 */
	public function test($email){
		$this->PHPMailer->addAddress($email);

		$this->PHPMailer->WordWrap = 50;
		$this->PHPMailer->isHTML(true);

		//开启调试模式
		$this->PHPMailer->SMTPDebug = 2;

		$this->PHPMailer->Subject = '邮件发送测试';
		$this->PHPMailer->Body = '007!007!这里是002，听到请回答!听到请回答，over٩(๑`н´๑)۶';

		if ($this->PHPMailer->send() !== false) {
			echo '<h1>邮件发送成功!</h1>';
		}else{
			echo '<h1>这里是002，无法联系到007，兹……</h1>';
		}
		$this->PHPMailer->ClearAddresses();
	}
}