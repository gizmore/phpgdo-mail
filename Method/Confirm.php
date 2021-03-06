<?php
namespace GDO\Mail\Method;

use GDO\Core\GDT_Token;
use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\User\GDT_User;
use GDO\Form\GDT_Submit;
use GDO\Mail\GDT_Email;
use GDO\User\GDO_User;
use GDO\Form\GDT_Validator;
use GDO\Mail\Module_Mail;
use GDO\Date\Time;

final class Confirm extends MethodForm
{
	public function isTrivial() : bool { return false; }
	
	public function createForm(GDT_Form $form): void
	{
		$form->addFields(
			GDT_User::make('user')->required(),
			GDT_Email::make('email')->required(),
			GDT_Token::make('token')->required(),
		);
		$form->addFields(
			GDT_Validator::make()->validatorFor($form, 'token', [$this, 'validateToken']),
		);
		$form->actions()->addField(GDT_Submit::make());
	}
	
	public function getUser() : GDO_User
	{
		return $this->gdoParameterValue('user');
	}
	
	public function getEmail() : string
	{
		return $this->gdoParameterVar('email');
	}
	
	public function getToken() : string
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
	
	public function formValidated(GDT_Form $form)
	{
		$email = $this->getEmail();
		$this->confirmMail($this->getUser(), $email);
		$this->message('msg_mail_confirmed', [html($email)]);
	}

	public function confirmMail(GDO_User $user, string $email)
	{
		$now = Time::getDate();
		$mod = Module_Mail::instance();
		$mod->saveUserSetting($user, 'email', $email);
		$mod->saveUserSetting($user, 'email_confirmed', $now);
	}



	
}
