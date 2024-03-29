<?php
namespace GDO\Mail\Method;

use GDO\Core\GDT;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\GDT_Validator;
use GDO\Form\MethodForm;
use GDO\Mail\Mail;
use GDO\Mail\Module_Mail;
use GDO\UI\GDT_Message;
use GDO\UI\GDT_Title;
use GDO\User\GDO_User;
use GDO\User\GDT_User;

/**
 * Send a mail to a user.
 *
 * @version 7.0.0
 * @since 6.2.0
 * @author gizmore
 */
final class Send extends MethodForm
{

    public function isCLI(): bool
    {
        return true;
    }


    public function getCLITrigger(): string
    {
        return 'sendmail';
    }

    protected function createForm(GDT_Form $form): void
	{
		if (GDO_User::current()->hasMail())
		{
			$form->text('info_send_mail');
			$form->addFields(
				GDT_User::make('user')->withCompletion()->notNull(),
				GDT_Title::make('title')->notNull(),
				GDT_Message::make('message')->notNull(),
				GDT_AntiCSRF::make(),
			);
			$form->addFields(
				GDT_Validator::make('validate_allowance')->validatorFor($form, 'user', [$this, 'validateAllowance']),
			);
			$form->actions()->addField(GDT_Submit::make());
		}
		else
		{
			$form->text('info_need_mail');
			$form->actions()->addField(GDT_Submit::make()->disabled());
		}
	}

	public function formValidated(GDT_Form $form): GDT
	{
		$from = GDO_User::current();
		$to = $form->getFormValue('user');
		$title = $form->getFormValue('title');
		/** @var $to GDO_User * */

		$mail = Mail::botMail();
		$mail->setReturn($from->getMail());
		$mail->setReturnName($from->renderUserName());
		$mail->setReceiver($to->getMail());
		$mail->setReceiverName($to->renderUserName());
		$mail->setSubject(t('mail_send_arbr_subj',
			[sitename(), $title, $from->renderUserName()]));

		$bodyArgs = [
			$to->renderUserName(),
			$from->renderUserName(),
			sitename(),
			$title,
			$form->getFormValue('message'),
		];
		$mail->setBody(t('mail_send_arbr_body', $bodyArgs));

		if (!$mail->sendToUser($to))
        {
            return $this->error('err_send_mail');
        }

		return $this->message('msg_arbr_mail_sent', [$to->renderUserName()]);
	}

	/**
	 * Validate if the user allows sending them an email.
	 */
	public function validateAllowance(GDT_Form $form, GDT $field, GDO_User $target = null): bool
	{
		if (!$target)
		{
            if ($field->hasError())
            {
                return false;
            }
			return $field->error('err_user');
		}
		if (!Module_Mail::instance()->userSettingValue($target, 'allow_email'))
		{
			return $field->error('err_user_does_not_want_mail');
		}
		if (!$target->hasMail())
		{
			return $field->error('err_user_does_not_has_mail');
		}
		if ($target === GDO_User::current())
		{
            if (!Module_Mail::instance()->cfgMailSelf())
            {
                return $field->error('err_arbr_mail_self');
            }
		}
		return true;
	}

}
