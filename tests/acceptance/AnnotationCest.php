
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
		$I->amOnPage('/srazvs/block/?id=2&cms=edit&page=block');
		$I->see('úprava bloku');
		$I->click('Náhled anotace', '#content');
		$I->seeInCurrentUrl('/srazvs/block/annotation/b16343baaecb05de0ce3b551e4944993');
		foreach ($this->annotationBlock['fields'] as $field => $value) {
			$I->seeInField($field, $value);
		}
	}

	public function ensure_that_program_annotation_works(\AcceptanceTester $I)
	{
		$I->wantTo('Ensure that programs annotation works');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/program/?id=1&cms=edit&page=program');
		$I->see('úprava programu');
		$I->click('Náhled anotace', '#content');
		$I->seeInCurrentUrl('/srazvs/program/annotation/d295d29771b5f104270bad365e5e4107');
		foreach ($this->annotationProgram['fields'] as $field => $value) {
			$I->seeInField($field, $value);
		}
	}

	public function block_annotation_should_be_accessible_by_public(\AcceptanceTester $I)
	{
		$I->wantTo('Ensure that blocks annotation is accesible by public');
		$I->amOnPage('/srazvs/block/annotation/b16343baaecb05de0ce3b551e4944993');
		foreach ($this->annotationBlock['fields'] as $field => $value) {
			$I->seeInField($field, $value);
		}
	}

	public function program_annotation_should_be_accessible_by_public(\AcceptanceTester $I)
	{
		$I->wantTo('Ensure that programs annotation is accesible by public');
		$I->amOnPage('/srazvs/program/annotation/d295d29771b5f104270bad365e5e4107');
		foreach ($this->annotationProgram['fields'] as $field => $value) {
			$I->seeInField($field, $value);
		}
	}

}
