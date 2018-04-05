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
	// @codingStandardsIgnoreStart
	public function translate($message, $count = null)
	{
		dd($message);
	}
	// @codingStandardsIgnoreEnd

}
