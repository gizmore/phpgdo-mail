<?php
namespace GDO\Mail;

use GDO\Core\GDT_String;
use GDO\Core\GDT_Template;
use GDO\User\GDO_User;

/**
 * Email field.
 *
 * @author gizmore
 * @version 7.0.0
 * @since 5.0.0
 */
class GDT_Email extends GDT_String
{
	public int $max = 170;

	public string $pattern = "/^[^@]+@[^@]+$/iD";

	public string $icon = 'email';

	public function getInputType() : string
	{
		return 'email';
	}
	
	public function isSearchable() : bool
	{
		return GDO_User::current()->isStaff();
	}
	
	public function isOrderable(): bool
	{
		return false;
	}

	public function defaultLabel(): self
	{
		return $this->label('email');
	}

	public function renderHTML(): string
	{
		return GDT_Template::php('Mail', 'email_html.php',
			[
				'field' => $this
			]);
	}

	public function plugVar(): string
	{
		$num = spl_object_id($this);
		return "gizmore_{$num}@wechall.net";
	}
	
	###############
	### Prefill ###
	###############
	public bool $currentUserMail = false;
	public function currentUserMail(bool $bool=true)
	{
		if ($this->currentUserMail = $bool)
		{
			$this->initial(Module_Mail::instance()->getUserMail());
		}
		return $this;
	}

}
