<?php
namespace GDO\Mail\lang;

return [
	'cfg_user_allow_email' => 'Allow people to email you?',
	'mt_mail_send' => 'Send a mail',
	'enum_html' => 'HTML',
	'enum_text' => 'Text',
	'info_send_mail' => 'Here you can send a mail to a user. Your own email will be known to them.',
	'info_need_mail' => 'You need an own email address to send mails to other users.',
	'err_user_does_not_want_mail' => 'This user does not want an email from you. This might be a general setting from them.',
	'err_user_does_not_has_mail' => 'This user does not have an email address.',
	'err_arbr_mail_self' => 'You cannot send emails to yourself via this method.',
	'msg_arbr_mail_sent' => 'The mail has been successfully sent to %s.',
	'mail_send_arbr_subj' => '[%s] %s - from %s',
	'mail_send_arbr_body' => 'Dear %s,

%s has sent you a mail via %s.

Title: %s
Message:
%s

Sincerly,
The %3$s system',

	# 7.0.0
	'mt_mail_change' => 'Change Email',
	'md_mail_change' => 'Change your Email Address',
	'msg_mail_changing' => 'You are about to change your email to `%s`. Please confirm this.',

	'mt_mail_validate' => 'Validate Email',
	'md_mail_validate' => 'Validate your Email Address',

	'email' => 'E-Mail',
	'email_confirmed' => 'Confirmed at',

	# Request validation
	'msg_mail_confirmed' => 'Your E-Mail has been confirmed and set to `%s`.',
	'mailt_confirm_email' => '%s: Confirm your E-Mail',
	'mailb_confirm_email' => '
Hello %s,

Please confirm your E-Mail address by visiting this link:

%s

Sincerly,
The %s Team',

	# 7.0.1
	'cfg_allow_email' => 'Allow email sending for users',

	'mt_mail_requestvalidation' => 'E-Mail Validation',
	'info_email_request_validation' => 'Here you can setup your account email.',

];
