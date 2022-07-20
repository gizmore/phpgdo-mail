<?php
namespace GDO\Mail;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\UI\GDT_Page;
use GDO\UI\GDT_Link;
use GDO\User\GDO_User;
use GDO\UI\GDT_Bar;
use GDO\Date\GDT_DateTime;
use GDO\Register\GDO_UserActivation;
use GDO\Date\Time;

/**
 * Mail stuff.
 * 
 * - Some user settings.
 * - Send function.
 * - Validate and Changemail
 * 
 * @author gizmore
 * @version 7.0.0
 * @since 6.0.0
 */
final class Module_Mail extends GDO_Module
{
    public int $priority = 30;
    
    public function getDependencies() : array
    {
    	return ['User', 'Mailer'];
    }
    
    public function getFriendencies() : array
    {
    	return ['Account'];
    }
    
    public function onLoadLanguage() : void
    {
        $this->loadLanguage('lang/mail');
    }
    
    public function getConfig() : array
    {
        return [
            GDT_Checkbox::make('allow_email')->initial('1'),
            GDT_Checkbox::make('show_in_sidebar')->initial('0'),
        ];
    }
    
    public function cfgSidebar() : string { return $this->getConfigVar('show_in_sidebar'); }
    public function cfgAllowEmail() : string { return $this->getConfigVar('allow_email'); }

    public function getUserConfig() : array
    {
    	return [
    		GDT_Email::make('email'),
    		GDT_DateTime::make('email_confirmed'),
    	];
    }
    
    public function getUserSettings() : array
    {
    	return [
    		GDT_Checkbox::make('allow_email')->initial('1')->label('cfg_user_allow_email'),
    		GDT_EmailFormat::make('email_format')->initial('html'),
    	];
    }
    
    public function cfgUserEmailConfirmed(GDO_User $user=null) : ?string
    {
    	if ($this->cfgUserEmailIsConfirmed($user))
    	{
    		return $this->cfgUserEmail($user);
    	}
    	return null;
    }
    public function cfgUserEmailIsConfirmed(GDO_User $user=null) : ?string
    {
    	$user = $user ? $user : GDO_User::current();
    	return $this->userSettingVar($user, 'email_confirmed');
    }
    public function cfgUserEmail(GDO_User $user=null) : ?string
    {
    	$user = $user ? $user : GDO_User::current();
    	return $this->userSettingVar($user, 'email');
    }
    public function cfgUserAllowEmail(GDO_User $user=null) : string
    {
    	$user = $user ? $user : GDO_User::current();
    	return $this->userSettingVar($user, 'allow_email');
    }
    public function cfgUserEmailFormat(GDO_User $user=null) : string
    {
    	$user = $user ? $user : GDO_User::current();
    	return $this->userSettingVar($user, 'email_format');
    }

    public function onInitSidebar() : void
    {
        if ($this->cfgSidebar())
        {
            if ($this->cfgAllowEmail())
            {
                GDT_Page::instance()->rightBar()->addField(
                    GDT_Link::make('ft_mail_send')->href(
                        href('Mail', 'Send')));
            }
        }
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
    
    public function hookUserActivated(GDO_User $user, GDO_UserActivation $activation)
    {
    	if ($email = $activation->getEmail())
    	{
    		$this->saveUserSetting($user, 'email', $email);
    		$this->saveUserSetting($user, 'email_confirmed', Time::getDate());
    	}
    }

}
