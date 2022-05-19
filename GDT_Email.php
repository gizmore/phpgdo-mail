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
	public int $max = 170; # Unique constraint

	public string $pattern = "/^[^@]+@[^@]+$/iD";
	public $icon = 'email';
	
	public bool $orderable = false;
	
	public function defaultLabel() : self { return $this->label('email'); }
	
	public function renderForm() : string { return GDT_Template::php('Mail', 'form/email.php', ['field' => $this]); }
	public function renderCell() : string { return GDT_Template::php('Mail', 'cell/email.php', ['field' => $this]); }

}
