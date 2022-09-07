<?php
namespace GDO\Mail\Method;

use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\Form\GDT_Submit;
use GDO\Mail\GDT_Email;
use GDO\User\GDO_User;

final class Change extends MethodForm
{
	public function createForm(GDT_Form $form): void
	{
		$form->addFields(
			GDT_Email::make('newmail')->notNull(),
		);
		$form->actions()->addField(GDT_Submit::make());
	}
	
	public function getNewMail() : string
	{
		return $this->gdoParameterVar('newmail');
	}
	
	public function formValidated(GDT_Form $form)
	{
		$user = GDO_User::current();
		$newmail = $this->getNewMail();
		$user->saveSettingVar('Mail', 'email', $newmail);
		$user->saveSettingVar('Mail', 'email_confirmed', null);
		$href = href('Mail', 'RequestValidation');
		return $this->redirectMessage('msg_mail_changing', [html($newmail)], $href);
	}
	
}
