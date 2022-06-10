<?php
namespace GDO\Mail\Method;

use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\Account\Module_Account;

final class Validate extends MethodForm
{
	public function beforeExecute() : void
	{
		if (module_enabled('Account'))
		{
			Module_Account::instance()->renderAccountBar();
		}
	}
	
	public function createForm(GDT_Form $form): void
	{
	}
	
}
