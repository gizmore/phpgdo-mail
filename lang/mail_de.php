<?php
namespace GDO\Mail\lang;
return [
	'cfg_user_allow_email' => 'Personen erlauben, Ihnen E-Mails zu senden?',
	'ft_mail_send' => 'Mail senden',
	'enum_html' => 'HTML',
	'enum_text' => 'Text',
	'info_send_mail' => 'Hier können Sie eine E-Mail an einen Benutzer senden. Ihre eigene E-Mail wird bekannt gegeben.',
	'info_need_mail' => 'Sie benötigen eine eigene E-Mail-Adresse, um Mails an andere Benutzer zu senden.',
	'err_user_does_not_want_mail' => 'Dieser Benutzer möchte keine E-Mail von Ihnen. Dies könnte die Grundeistellung sein.',
	'err_user_does_not_has_mail' => 'Dieser Benutzer hat keine E-Mail-Adresse.',
	'err_arbr_mail_self' => 'Sie können mit dieser Methode keine E-Mails an sich selbst senden.',
	'msg_arbr_mail_sent' => 'Die E-Mail wurde erfolgreich an %s gesendet.',
	'mail_send_arbr_subj' => '[%s] %s - von %s',
	'mail_send_arbr_body' => 'Lieber %s,
	
%s hat Ihnen eine E-Mail über %s gesendet.
	
Titel: %s
Nachricht:
%s
	
Mit freundlichen Grüßen
Das %3$s-System',

	# 7.0.0
	'mt_mail_change' => 'E-Mail Ändern',
	'md_mail_change' => 'Ändern Sie Ihre E-Mail Adresse',
	
	'mt_mail_validate' => 'E-Mail Bestätigen',
	'md_mail_validate' => 'Validieren Sie Ihre E-Mail Adresse',
	
	'email' => 'E-Mail',
	'email_confirmed' => 'Bestätigt am',
	
	# Request validation
	'mailt_confirm_email' => '%s: Confirm your E-Mail',
	'mailb_confirm_email' => '
Hello %s,
	
Please confirm your E-Mail address by visiting this link:
	
%s
	
Sincerly,
The %s Team',
	
	# 7.0.1
	'cfg_allow_email' => 'Allow email sending for users',
];
