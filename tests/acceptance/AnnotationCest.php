
<?php

class AnnotationCest extends CestCase
{

	private $annotationBlock = [
		'fields' => [
			'name'			=> '20:00 - Zahájení srazu',
			'description'	=> '',
			'tutor'			=> 'Tomáš Litera',
			'email'			=> 'tomaslitera@hotmail.com',
			'capacity'		=> '0',
		],
	];

	private $annotationProgram = [
		'fields' => [
			'name'			=> 'Hry a hříčky',
			'description'	=> 'sflvbhflevblf',
			'material'		=> '',
			'tutor'			=> '',
			'email'			=> 'tomaslitera@hotmail.com,tomas.litera@gmail.com',
			'capacity'		=> '200',
		],
	];

	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests
	public function ensure_that_block_annotation_works(\AcceptanceTester $I)
	{
		$I->wantTo('Ensure that blocks annotation works');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/block/edit/2?page=block');
		$I->see('úprava bloku');
		$I->click('Náhled anotace', '#content');
		$I->seeInCurrentUrl('/srazvs/block/annotation/382ff1c792c7980aa0b1950259a518e8');
		foreach ($this->annotationBlock['fields'] as $field => $value) {
			$I->seeInField($field, $value);
		}
	}

	public function ensure_that_program_annotation_works(\AcceptanceTester $I)
	{
		$I->wantTo('Ensure that programs annotation works');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/program/edit/1?page=program');
		$I->see('úprava programu');
		$I->click('Náhled anotace', '#content');
		$I->seeInCurrentUrl('/srazvs/program/annotation/524888c93f896f388d563f68682cf41c');
		foreach ($this->annotationProgram['fields'] as $field => $value) {
			$I->seeInField($field, $value);
		}
	}

	public function block_annotation_should_be_accessible_by_public(\AcceptanceTester $I)
	{
		$I->wantTo('Ensure that blocks annotation is accesible by public');
		$I->amOnPage('/srazvs/block/annotation/382ff1c792c7980aa0b1950259a518e8');
		foreach ($this->annotationBlock['fields'] as $field => $value) {
			$I->seeInField($field, $value);
		}
	}

	public function program_annotation_should_be_accessible_by_public(\AcceptanceTester $I)
	{
		$I->wantTo('Ensure that programs annotation is accesible by public');
		$I->amOnPage('/srazvs/program/annotation/524888c93f896f388d563f68682cf41c');
		foreach ($this->annotationProgram['fields'] as $field => $value) {
			$I->seeInField($field, $value);
		}
	}

}
