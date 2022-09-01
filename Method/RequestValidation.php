<?php
namespace GDO\Mail\Method;

use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Submit;
use GDO\User\GDO_User;
use GDO\Mail\GDT_Email;
use GDO\Mail\Mail;
use GDO\UI\GDT_Link;
use GDO\Account\Module_Account;
use GDO\Core\GDT_Token;
use GDO\Mail\Module_Mail;

/**
 * Send an email confirmation mail to a user.
 * 
 * @author gizmore
 */
final class RequestValidation extends MethodForm
{
	public function onRenderTabs() : void
	{
		if (module_enabled('Account'))
		{
			Module_Account::instance()->renderAccountBar();
		}
	}
	
	public function createForm(GDT_Form $form): void
	{
		$user = GDO_User::current();
		$form->text('info_email_request_validation');
		$mail = $user->getMail(false);
		$field = GDT_Email::make('_email')->initial($mail)->required();
		$form->addFields($field, GDT_AntiCSRF::make());
		$form->actions()->addField(GDT_Submit::make());
	}
	
	public function formValidated(GDT_Form $form)
	{
		$user = GDO_User::current();
		$this->sendMail($user, $form->getFormVar('_email'));
	}
	
	public function sendMail(GDO_User $user, string $email)
	{
		$mod = Module_Mail::instance();
		$mail = Mail::botMail();
		$mail->setReceiver($email);
		$mail->setReceiverName($user->renderUserName());
		$mail->setSubject(t('mailt_confirm_email', [sitename()]));
		$uid = $user->getID();
		$token = GDT_Token::generateToken($email . $uid);
		$append = sprintf('&user=%d&_email=%s&token=%s&submit=1',
			$uid, urlencode($email), $token);
		$link = GDT_Link::absolute(href('Mail', 'Confirm', $append));
		$args = [
			$user->renderUserName(),
			$link,
			sitename(),
		];
		$mail->setBody(t('mailb_confirm_email', $args));
		$format = $mod->cfgUserEmailFormat($user);
		$mail->sendAsFormat($format);
	}

}
