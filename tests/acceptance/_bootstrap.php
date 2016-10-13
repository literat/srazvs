<?php
// Here you can initialize variables that will be available to your tests

/**
* Default cest case
*/
class CestCase
{

	protected function fillForm(\AcceptanceTester $I, array $formData)
	{
		if(array_key_exists('fields', $formData)) {
			foreach($formData['fields'] as $field => $value) {
				$I->fillField($field, $value);
			}
		}

		if(array_key_exists('options', $formData)) {
			foreach($formData['options'] as $option => $value) {
				$I->selectOption($option, $value);
			}
		}
	}

	protected function seeInForm(\AcceptanceTester $I, array $formData)
	{
		if(array_key_exists('fields', $formData)) {
			foreach($formData['fields'] as $field => $value) {
				$I->seeInField($field, $value);
			}
		}

		if(array_key_exists('options', $formData)) {
			foreach($formData['options'] as $option => $value) {
				$I->seeOptionIsSelected($option, $value);
			}
		}
	}

}
