<?php
namespace GDO\Mail;

use GDO\Core\GDT_Enum;

/**
 * Enum that switches between text und html format.
 *
 * @version 7.0.1
 * @since 5.0
 * @author gizmore
 */
final class GDT_EmailFormat extends GDT_Enum
{

	public const TEXT = 'text';
	public const HTML = 'html';

	protected function __construct()
	{
		parent::__construct();
		$this->icon('format');
		$this->enumValues(self::TEXT, self::HTML);
	}

	public function defaultLabel(): self
	{
		return $this->label('email_fmt');
	}

}
