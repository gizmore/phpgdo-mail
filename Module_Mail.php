<?php
namespace GDO\Mail;

use GDO\Core\GDO_Module;
use GDO\Core\GDT;
use GDO\Core\GDT_Checkbox;
use GDO\Date\GDT_DateTime;
use GDO\Date\Time;
use GDO\Register\GDO_UserActivation;
use GDO\UI\GDT_Bar;
use GDO\UI\GDT_Link;
use GDO\UI\GDT_Page;
use GDO\User\GDO_User;
use GDO\User\GDT_ACLRelation;

/**
 * Mail stuff.
 * Meanwhile it split between Mail (for GDO/GDT) and multiple Mailer Providers.
 *
 * - Some user settings.
 * - Validate and Changemail
 *
 * @version 7.0.1
 * @since 6.0.0
 * @author gizmore
 */
final class Module_Mail extends GDO_Module
{

	public int $priority = 30;

	public static function displayMailLink(GDO_User $user): string
	{
		if ($mail = $user->getMail())
		{
			return "<a href=\"mailto:{$mail}\">{$mail}</a>";
		}
		return GDT::EMPTY_STRING;
	}

	public function getDependencies(): array
	{
		return ['Mailer', 'Net'];
	}

	public function getFriendencies(): array
	{
		return ['Account'];
	}

	public function onLoadLanguage(): void
	{
		$this->loadLanguage('lang/mail');
	}

	public function getConfig(): array
	{
		return [
			GDT_Checkbox::make('allow_email')->initial('1'),
			GDT_Checkbox::make('hook_sidebar')->initial('0'),
		];
	}

	public function getUserConfig(): array
	{
		return [
			GDT_Email::make('email'),
			GDT_DateTime::make('email_confirmed')->noacl(),
		];
	}

	public function getACLDefaults(): array
	{
		return [
			'email_format' => [GDT_ACLRelation::HIDDEN, '0', null],
		];
	}

	public function getUserSettings(): array
	{
		return [
			GDT_Checkbox::make('allow_email')->initial('1')->label('cfg_user_allow_email')->noacl(),
			GDT_EmailFormat::make('email_format')->initial('html')->noacl(),
		];
	}

	public function onInitSidebar(): void
	{
		if ($this->cfgSidebar())
		{
			if ($this->cfgAllowEmail())
			{
				GDT_Page::instance()->rightBar()->addField(
					GDT_Link::make('mt_mail_send')->href(
						href('Mail', 'Send')));
			}
		}
	}

	public function cfgSidebar(): string { return $this->getConfigVar('hook_sidebar'); }

	public function cfgAllowEmail(): string { return $this->getConfigVar('allow_email'); }

	public function cfgUserAllowEmail(GDO_User $user = null): string
	{
		$user = $user ? $user : GDO_User::current();
		return $this->userSettingVar($user, 'allow_email');
	}

	public function cfgUserEmailFormat(GDO_User $user = null): string
	{
		$user = $user ? $user : GDO_User::current();
		return $this->userSettingVar($user, 'email_format');
	}

	public function hookAccountBar(GDT_Bar $nav)
	{
		if ($this->cfgUserEmailConfirmed())
		{
			$nav->addField(GDT_Link::make('mt_mail_change')->href(href('Mail', 'Change')));
		}
		else
		{
			$nav->addField(GDT_Link::make('mt_mail_validate')->href(href('Mail', 'RequestValidation')));
		}
	}

	public function cfgUserEmailConfirmed(GDO_User $user = null): ?string
	{
		if ($this->cfgUserEmailIsConfirmed($user))
		{
			return $this->cfgUserEmail($user);
		}
		return null;
	}

	public function cfgUserEmailIsConfirmed(GDO_User $user = null): ?string
	{
		$user = $user ? $user : GDO_User::current();
		return $this->userSettingVar($user, 'email_confirmed');
	}

	public function cfgUserEmail(GDO_User $user = null): ?string
	{
		$user = $user ? $user : GDO_User::current();
		return $this->userSettingVar($user, 'email');
	}

	public function hookUserActivated(GDO_User $user, GDO_UserActivation $activation = null)
	{
		if ($activation !== null)
		{
			if ($email = $activation->getEmail())
			{
				$this->saveUserSetting($user, 'email', $email);
				$this->saveUserSetting($user, 'email_confirmed', Time::getDate());
			}
		}
	}

}
