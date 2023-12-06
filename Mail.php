<?php
namespace GDO\Mail;

use GDO\CLI\CLI;
use GDO\Core\Application;
use GDO\Core\Debug;
use GDO\Core\GDT_Template;
use GDO\Mailer\Mailer;
use GDO\UI\GDT_HTML;
use GDO\UI\GDT_Page;
use GDO\UI\TextStyle;
use GDO\User\GDO_User;
use GDO\Util\Strings;

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
 * @version 7.0.1
 * @since 1.0.0
 * *@author Inferno (1.0.0)
 * @author gizmore
 *
 */
final class Mail
{

	public const GPG_PASSPHRASE = ''; # perf
	public const GPG_FINGERPRINT = '';
	public static $SENT = 0;
	public static $DEBUG = GDO_DEBUG_EMAIL; #GDO_EMAIL_GPG_SIG_PASS;
	public static $ENABLE = GDO_ENABLE_EMAIL; #GDO_EMAIL_GPG_SIG;
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

	/**
	 * Create a new mail from the GDO bot.
	 */
	public static function botMail(): self
	{
		$mail = new self();
		$mail->setSender(GDO_BOT_EMAIL);
		$mail->setSenderName(GDO_BOT_NAME);
		return $mail;
	}

	public function setSender($s) { $this->sender = $s; }

	public function setSenderName($sn) { $this->senderName = $sn; }

	public static function sendDebugMail($subject, $body)
	{
		$to = def('GDO_ERROR_EMAIL', GDO_ADMIN_EMAIL);
		return self::sendMailS(GDO_BOT_EMAIL, $to, GDO_SITENAME . ': ' . $subject, Debug::getDebugText($body), true, true);
	}

	public static function sendMailS($sender, $receiver, $subject, $body, $html = false, $resendCheck = false)
	{
		$mail = new self();
		$mail->setSender($sender);
		$mail->setReceiver($receiver);
		$mail->setSubject($subject);
		$mail->setBody($body);
// 		$mail->setResendCheck($resendCheck);
		return $html ? $mail->sendAsHTML() : $mail->sendAsText();
	}

	public function setReceiver($r) { $this->receiver = $r; }

	public function setSubject($s) { $this->subject = $this->escapeHeader($s); }

	public function escapeHeader($h) { return str_replace("\r", '', str_replace("\n", '', $h)); }

	public function setBody($b) { $this->body = $b; }

	public function sendAsHTML($cc = '', $bcc = '')
	{
		$this->format = 'html';
		return $this->send($cc, $bcc, $this->nestedHTMLBody(), true);
	}

	public function send($cc, $bcc, $message, $html = true)
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

	private function printMailToDebugIt(): void
    {
		if (Application::instance()->isUnitTests())
		{
			echo TextStyle::bold("A mail has been sent!\n");
		}
		else
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

	public function nestedHTMLBody(): string
	{
		$body = $this->body;
		if (class_exists('GDO\Util\Strings', true))
		{
			$body = Strings::nl2brHTMLSafe($body);
		}
		if (!class_exists('GDO\Core\GDT_Template', true))
		{
			return $body;
		}
		$tVars = [
			'content' => $body,
		];
		return GDT_Template::php('Mail', 'mail.php', $tVars);
	}

	public function nestedTextBody(): string
	{
		return CLI::htmlToCLI($this->body);
	}

	public function sendAsText($cc = '', $bcc = ''): bool
	{
		$this->format = 'text';
		return $this->send($cc, $bcc, $this->nestedTextBody(), false);
	}

// 	public function setResendCheck($bool) { $this->resendCheck = $bool; }

	public function isHTML() { return $this->format === 'html'; }

	public function setReply($r): void { $this->reply = $r; }

	public function setReplyName($rn) { $this->replyName = $rn; }

	public function setReturn($r) { $this->return = $r; }

	###########
	### Get ###
	###########

	public function setReturnName($rn) { $this->returnName = $rn; }

	public function setGPGKey($k) { $this->gpgKey = $k; }

	public function setAllowGPG($bool) { $this->allowGPG = $bool; }

	public function removeAttachment($title) { unset($this->attachments[$title]); }

	public function addAttachmentFile($title, $filename)
	{
		$mime = mime_content_type($filename);
		$data = file_get_contents($filename);
		return $this->addAttachment($title, $data, $mime);
	}

	public function addAttachment($title, $data, $mime = 'application/octet-stream', $encrypted = true) { $this->attachments[$title] = [$data, $mime, $encrypted]; }

	public function getUTF8Reply()
	{
		if ($this->reply === '')
		{
			return $this->getUTF8Sender();
		}
		return $this->getUTF8($this->reply, $this->replyName);
	}

	public function getUTF8Sender()
	{
		return $this->getUTF8($this->sender, $this->senderName);
	}

	public function getUTF8($email, $name)
	{
		return $name === '' ? $email : '"' . $this->getUTF8Encoded($name) . "\" <{$email}>";
	}

	public function getUTF8Encoded($string) { return '=?UTF-8?B?' . base64_encode($string) . '?='; }

	public function getUTF8Return()
	{
		if ($this->reply === '')
		{
			return $this->getUTF8Sender();
		}
		return $this->getUTF8($this->return, $this->returnName);
	}

	public function getUTF8Receiver()
	{
		return $this->getUTF8($this->receiver, $this->receiverName);
	}

	############
	### Send ###
	############

	public function getUTF8Subject() { return $this->getUTF8Encoded($this->subject); }

	public function getAttachments() { return $this->attachments; }

	public function getCC() { return $this->cc; }

	public function setCC($cc) { $this->cc = $cc; }

	public function getBCC() { return $this->bcc; }

	public function setBCC($bcc) { $this->bcc = $bcc; }

	/**
	 * This requires a GDO_User and chooses preferences.
	 * Simply do not call it when you use Mail as standalone.
	 *
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

	public function setReceiverName($rn) { $this->receiverName = $rn; }

	public function sendAsFormat(string $format): bool
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

}
