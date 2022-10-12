<?php
namespace GDO\Mail;

use GDO\Core\Debug;
use GDO\Core\GDT_Template;
use GDO\User\GDO_User;
use GDO\CLI\CLI;
use GDO\Util\Strings;
use GDO\Mailer\Mailer;
use GDO\UI\GDT_Page;
use GDO\UI\GDT_HTML;

/**
 * Will send very simple html and plaintext mails.
 * Supports GPG signing and encryption.
 * Uses UTF8 encoding and features attachments.
 * 
 * @TODO: Rewrite
 * 
 * @TODO: Implement cc and bcc
 * @TODO: Make use of staff cc?
 * @TODO: Test Attechments in combination with GPG
 * @TODO: Implement GPG Signatures
 * 
 * @author Inferno (1.0.0)
 * @author gizmore
 * 
 * @version 7.0.1
 * @since 1.0.0
 * */
final class Mail
{
	public static $SENT = 0; # perf
	public static $DEBUG = GDO_DEBUG_EMAIL;
	public static $ENABLE = GDO_ENABLE_EMAIL;
	
	const GPG_PASSPHRASE = ''; #GDO_EMAIL_GPG_SIG_PASS;
	const GPG_FINGERPRINT = ''; #GDO_EMAIL_GPG_SIG;

	private $reply = '';
	private $replyName = '';
	private $receiver = '';
	private $receiverName = '';
	private $return = '';
	private $returnName = '';
	private $sender = '';
	private $senderName = '';
	private $subject = '';
	private $body = '';
	private $attachments = [];
	private $headers = [];
	private $gpgKey = '';
	private $resendCheck = false;
	
	private $cc = '';
	private $bcc = '';
	private $format = 'html'; # will send both anyway?

	private $allowGPG = true;
	
	public function isHTML() { return $this->format === 'html'; }

	public function setCC($cc) { $this->cc = $cc; }
	public function setBCC($bcc) { $this->bcc = $bcc; }
	public function setReply($r) { $this->reply = $r; }
	public function setReplyName($rn) { $this->replyName = $rn; }
	public function setSender($s) { $this->sender = $s; }
	public function setSenderName($sn) { $this->senderName = $sn; }
	public function setReturn($r) { $this->return = $r; }
	public function setReturnName($rn) { $this->returnName = $rn; }
	public function setReceiver($r) { $this->receiver = $r; }
	public function setReceiverName($rn) { $this->receiverName = $rn; }
	public function setSubject($s) { $this->subject = $this->escapeHeader($s); }
	public function setBody($b) { $this->body = $b; }
	public function setGPGKey($k) { $this->gpgKey = $k; }
	public function setAllowGPG($bool) { $this->allowGPG = $bool; }
	public function setResendCheck($bool) { $this->resendCheck = $bool; }
	public function addAttachment($title, $data, $mime='application/octet-stream', $encrypted=true) { $this->attachments[$title] = array($data, $mime, $encrypted); }
	public function removeAttachment($title) { unset($this->attachments[$title]); }
	
	public function escapeHeader($h) { return str_replace("\r", '', str_replace("\n", '', $h)); }
	
	public function addAttachmentFile($title, $filename)
	{
		$mime = mime_content_type($filename);
		$data = file_get_contents($filename);
		return $this->addAttachment($title, $data, $mime);
	}
	
	###########
	### Get ###
	###########
	public function getUTF8Reply()
	{
		if ($this->reply === '')
		{
			return $this->getUTF8Sender();
		}
		return $this->getUTF8($this->reply, $this->replyName);
	}
	
	public function getUTF8Return()
	{
		if ($this->reply === '')
		{
			return $this->getUTF8Sender();
		}
		return $this->getUTF8($this->return, $this->returnName);
	}
	
	public function getUTF8($email, $name)
	{
		return $name === '' ? $email : '"'.$this->getUTF8Encoded($name)."\" <{$email}>";
	}
	
	public function getUTF8Sender()
	{
		return $this->getUTF8($this->sender, $this->senderName);
	}
	
	public function getUTF8Receiver()
	{
		return $this->getUTF8($this->receiver, $this->receiverName);
	}

	public function getUTF8Subject() { return $this->getUTF8Encoded($this->subject); }
	
	public function getUTF8Encoded($string) { return '=?UTF-8?B?'.base64_encode($string).'?='; }
	
	public function getAttachments() { return $this->attachments; }
	
	public function nestedHTMLBody()
	{
		if (!class_exists('GDO\Core\GDT_Template', true))
		{
		    if (class_exists('GDO\Util\Strings', true))
		    {
		        return Strings::nl2brHTMLSafe($this->body);
		    }
		    return $this->body;
		}
		$tVars = [
			'content' => $this->body,
		];
		return GDT_Template::php('Mail', 'mail.php', $tVars);
	}

	public function nestedTextBody()
	{
	    return CLI::htmlToCLI($this->body);
	}
	
	public function getCC() { return $this->cc; }
	public function getBCC() { return $this->bcc; }
	
	############
	### Send ###
	############
	/**
	 * Create a new mail from the GDO bot.
	 */
	public static function botMail() : self
	{
		$mail = new self();
		$mail->setSender(GDO_BOT_EMAIL);
		$mail->setSenderName(GDO_BOT_NAME);
		return $mail;
	}
	
	public static function sendMailS($sender, $receiver, $subject, $body, $html=false, $resendCheck=false)
	{
		$mail = new self();
		$mail->setSender($sender);
		$mail->setReceiver($receiver);
		$mail->setSubject($subject);
		$mail->setBody($body);
		$mail->setResendCheck($resendCheck);
		return $html ? $mail->sendAsHTML() : $mail->sendAsText();
	}
	
	public static function sendDebugMail($subject, $body)
	{
		$to = defined('GDO_ERROR_EMAIL') ? GDO_ERROR_EMAIL : GDO_ADMIN_EMAIL;
		return self::sendMailS(GDO_BOT_EMAIL, $to, GDO_SITENAME.": ".$subject, Debug::getDebugText($body), true, true);
	}
	
	/**
	 * This requires a GDO_User and chooses preferences.
	 * Simply do not call it when you use Mail as standalone.
	 * @param GDO_User $user
	 */
	public function sendToUser(GDO_User $user)
	{
		$mod = Module_Mail::instance();
		if ($mail = $mod->cfgUserEmail($user))
		{
			$this->setReceiver($mail);
			$this->setReceiverName($user->renderUserName());
			
// 			$this->setupGPG($user);
			
			$format = $mod->cfgUserEmailFormat($user);
			return $this->sendAsFormat($format);
		}
	}

	public function sendAsFormat(string $format) : bool
	{
		if ($format === 'text')
		{
			return $this->sendAsText();
		}
		else
		{
			return $this->sendAsHTML();
		}
	}
	
	public function sendAsText($cc='', $bcc='')
	{
		$this->format = 'text';
		return $this->send($cc, $bcc, $this->nestedTextBody(), false);
	}

	public function sendAsHTML($cc='', $bcc='')
	{
		$this->format = 'html';
		return $this->send($cc, $bcc, $this->nestedHTMLBody(), true);
	}

	public function send($cc, $bcc, $message, $html=true)
	{
		$this->setCC($cc);
		$this->setBCC($bcc);
		self::$SENT++;
		if (self::$DEBUG)
		{
			$this->printMailToDebugIt();
		}
		elseif (module_enabled('Mail'))
		{
			if (self::$ENABLE)
			{
				return Mailer::send($this);
			}
		}
		return false;
	}
	
	private function printMailToDebugIt()
	{
		$printmail = sprintf('<h1>Local HTML EMail to %s:</h1><div>%s<br/>%s</div>', htmlspecialchars($this->receiver),
			htmlspecialchars($this->subject), $this->nestedHTMLBody());
		$html = GDT_HTML::make()->var($printmail);
		
		$printmail = sprintf('<h1>Local Text EMail to %s:</h1><pre>%s<br/><br/>%s</pre>', htmlspecialchars($this->receiver),
			htmlspecialchars($this->subject), $this->nestedTextBody());
		$text = GDT_HTML::make()->var($printmail);
		
		GDT_Page::instance()->topResponse()->addFields($text, $html);
	}
	
}
