<?php
declare(strict_types=1);
namespace GDO\Mail;

use GDO\Core\GDT_String;
use GDO\User\GDO_User;

/**
 * Email field.
 *
 * @version 7.0.3
 * @since 5.0.0
 * @author gizmore
 */
class GDT_Email extends GDT_String
{

	public ?int $max = 170;

	public string $pattern = '/^[^@]+@[^@]+$/iD';

	public string $icon = 'email';
	public bool $currentUserMail = false;

    public bool $searchable = false;

	public function getInputType(): string
	{
		return 'email';
	}

	public function isSearchable(): bool
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
		if (!($mail = $this->getVar()))
		{
			return self::none();
		}
		return "<a class=\"gdo-email\" href=\"mailto:{$mail}\">{$mail}</a>";
	}

	###############
	### Prefill ###
	###############

	public function plugVars(): array
	{
		return [
			[$this->getName() => 'gizmore@wechall.net'],
		];
	}

	public function currentUserMail(bool $bool = true)
	{
		if ($this->currentUserMail = $bool)
		{
			$this->initial(Module_Mail::instance()->cfgUserEmail());
		}
		return $this;
	}

}
