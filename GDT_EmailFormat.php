<?php
declare(strict_types=1);
namespace GDO\Mail;

use GDO\Core\GDT_Enum;

/**
 * Enum that switches between text und html format.
 *
 * @version 7.0.3
 * @since 5.0
 * @author gizmore
 */
final class GDT_EmailFormat extends GDT_Enum
{

	final public const TEXT = 'text';
	final public const HTML = 'html';

	protected function __construct()
	{
		parent::__construct();
		$this->icon('format');
		$this->enumValues(self::TEXT, self::HTML);
		$this->notNull();
	}

	public function gdtDefaultLabel(): ?string
	{
		return 'email_fmt';
	}

}
