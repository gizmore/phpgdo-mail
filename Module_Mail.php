<?php
namespace GDO\Mail;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\UI\GDT_Page;
use GDO\UI\GDT_Link;

/**
 * - Mail stuff.
 * - Some user settings.
 * - Send function.
 * 
 * @author gizmore
 *
 */
final class Module_Mail extends GDO_Module
{
    public int $priority = 30;
    
    public function getDependencies() : array
    {
    	return ['Mailer'];
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

    public function getUserSettings() : array
    {
        return [
        	GDT_Email::make('email'),
            GDT_Checkbox::make('allow_email')->initial('1')->label('cfg_user_allow_email'),
            GDT_EmailFormat::make('email_format')->initial('html'),
        ];
    }
    
    public function onInitSidebar() : void
    {
        if ($this->cfgSidebar())
        {
            if ($this->cfgAllowEmail())
            {
                GDT_Page::$INSTANCE->rightNav->addField(
                    GDT_Link::make('ft_mail_send')->href(
                        href('Mail', 'Send')));
            }
        }
    }
    
}
