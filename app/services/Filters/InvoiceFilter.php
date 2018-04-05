<?php

namespace App\Services\Filters;

use Nette\SmartObject;

class InvoiceFilter
{

	use SmartObject;

	/**
	 * @param  integer $value
	 * @return string
	 */
	public function __invoke($value)
	{
		return sprintf("%03d", $value);
	}

}
