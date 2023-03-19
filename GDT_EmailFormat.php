<?php
namespace GDO\Mail;

use GDO\Core\GDT_Enum;

/**
 * Enum that switches between text und html format.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 5.0
 */
final class GDT_EmailFormat extends GDT_Enum
{
	const TEXT = 'text';
	const HTML = 'html';
	
	public function defaultLabel(): static
	{
		return $this->label('email_fmt');
	}
	
	protected function __construct()
	{
		parent::__construct();
		$this->icon('format');
		$this->enumValues(self::TEXT, self::HTML);
	}

}
