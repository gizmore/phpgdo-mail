<?php
namespace GDO\Mail;

use GDO\Core\GDT_String;
use GDO\Core\GDT_Template;

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

	public function isOrderable(): bool
	{
		return false;
	}

	public function defaultLabel(): self
	{
		return $this->label('email');
	}

	public function renderForm(): string
	{
		return GDT_Template::php('Mail', 'form/email.php',
			[
				'field' => $this
			]);
	}

	public function renderHTML(): string
	{
		return GDT_Template::php('Mail', 'cell/email.php',
			[
				'field' => $this
			]);
	}

	public function plugVar(): string
	{
		return 'gizmore@wechall.net';
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
