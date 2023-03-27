<?php
namespace GDO\Mail\Method;

use GDO\Core\GDT;
use GDO\Core\GDT_Token;
use GDO\Date\Time;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\GDT_Validator;
use GDO\Form\MethodForm;
use GDO\Mail\GDT_Email;
use GDO\Mail\Module_Mail;
use GDO\User\GDO_User;
use GDO\User\GDT_User;

final class Confirm extends MethodForm
{

	public function isTrivial(): bool { return false; }

	public function createForm(GDT_Form $form): void
	{
		$form->addFields(
			GDT_User::make('user')->notNull(),
			GDT_Email::make('_email')->notNull(),
			GDT_Token::make('token')->notNull(),
		);
		$form->addFields(
			GDT_Validator::make()->validatorFor($form, 'token', [$this, 'validateToken']),
		);
		$form->actions()->addField(GDT_Submit::make());
	}

	public function formValidated(GDT_Form $form): GDT
	{
		$email = $this->getEmail();
		$this->confirmMail($this->getUser(), $email);
		$this->message('msg_mail_confirmed', [html($email)]);
	}

	public function getEmail(): string
	{
		return $this->gdoParameterVar('_email');
	}

	public function confirmMail(GDO_User $user, string $email)
	{
		$now = Time::getDate();
		$mod = Module_Mail::instance();
		$mod->saveUserSetting($user, 'email', $email);
		$mod->saveUserSetting($user, 'email_confirmed', $now);
	}

	public function getUser(): GDO_User
	{
		return $this->gdoParameterValue('user');
	}

	public function getToken(): string
	{
		return $this->gdoParameterVar('token');
	}

	public function validateToken(GDT_Form $form, GDT_Token $field, $value)
	{
		$email = $this->getEmail();
		$uid = $this->getUser()->getID();
		if (!GDT_Token::validateToken($field->getVar(), $email . $uid))
		{
			return $field->error('err_invalid_token');
		}
		return true;
	}

}
