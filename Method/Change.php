<?php
namespace GDO\Mail\Method;

use GDO\Core\GDT;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Mail\GDT_Email;
use GDO\User\GDO_User;

final class Change extends MethodForm
{

	protected function createForm(GDT_Form $form): void
	{
		$form->text('info_change_mail');
		$form->addFields(
			GDT_Email::make('newmail')->notNull(),
			GDT_AntiCSRF::make(),
		);
		$form->actions()->addField(GDT_Submit::make());
	}

	public function formValidated(GDT_Form $form): GDT
	{
		$user = GDO_User::current();
		$newmail = $this->getNewMail();
		$user->saveSettingVar('Mail', 'email', $newmail);
		$user->saveSettingVar('Mail', 'email_confirmed', null);
		$href = href('Mail', 'RequestValidation');
		return $this->redirectMessage('msg_mail_changing', [html($newmail)], $href);
	}

	public function getNewMail(): string
	{
		return $this->gdoParameterVar('newmail');
	}

}
