<?php

namespace App\Services\Translators;

use Nette\Localization\ITranslator;

class CsTranslator implements ITranslator
{
	/**
	 * Translates the given string.
	 * @param  string   message
	 * @param  int      plural count
	 * @return string
	 */
	public function translate($message, $count = NULL)
	{
		dd($message);
		return ;
	}
}
