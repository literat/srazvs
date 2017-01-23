<?php

namespace App\Services\Filters;

use Nette\Object;

class InvoiceFilter extends Object
{

	/**
	 * @param  integer $value
	 * @return string
	 */
	public function __invoke($value)
	{
		return sprintf("%03d", $value);
	}

}
