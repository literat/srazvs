<?php

namespace App\Components\Forms;

use App\Components\BaseControl;
use Nette\Application\UI\Form;
use Nette\Forms\Controls;

abstract class BaseForm extends BaseControl
{
	const TEMPLATE_DIR = __DIR__ . '/../../templates/components/Forms';

	/**
	 * @param  Form $form
	 * @return Form
	 */
	protected function setupRendering(Form $form): Form
	{
		// setup form rendering
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = null;
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

		// make form and controls compatible with Twitter Bootstrap
		$form->getElementPrototype()->class('form-horizontal');
		foreach ($form->getControls() as $control) {
			if ($control instanceof Controls\Button) {
				$control->getControlPrototype()
					->addClass(empty($usedPrimary) ? 'btn btn-default' : '');
				$usedPrimary = true;
			} elseif ($control instanceof Controls\TextBase ||
				$control instanceof Controls\SelectBox ||
				$control instanceof Controls\MultiSelectBox
			) {
				$control->getControlPrototype()
					->addClass('form-control');
			} elseif ($control instanceof Controls\Checkbox ||
				$control instanceof Controls\CheckboxList ||
				$control instanceof Controls\RadioList
			) {
				$control->getSeparatorPrototype()
					->setName('div')
					->addClass($control->getControlPrototype()->type);
			}
		}

		return $form;
	}
}
