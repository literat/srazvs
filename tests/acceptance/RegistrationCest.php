<?php

class RegistrationCest
{

	private $successVisitor = [
		'fields' => [
			'name'			=> 'robo',
			'surname'		=> 'Tester',
			'nick'			=> 'roboTester',
			'email'			=> 'robo@tester.local',
			'birthday'		=> '27.04.1976',
			'street'		=> 'Testovací 32',
			'city'			=> 'Testákov 4',
			'postal_code'	=> '98765',
			'group_num'		=> '1A2.34',
			'group_name'	=> 'Testeři',
			'troop_name'	=> 'klikači',
			'arrival'		=> 'Někdy',
			'departure'		=> 'jindy',
			'comment'		=> 'než',
			'question'		=> 'právě',
			'question2'		=> 'teď!',
		],
		'options' => [
			'province'		=> 'Hlavní město Praha',
			'fry_dinner'	=> 'ano',
			'sat_breakfast'	=> 'ne',
			'sat_lunch'		=> 'ano',
			'sat_dinner'	=> 'ne',
			'sun_breakfast'	=> 'ano',
			'sun_lunch'		=> 'ne',
		],
	];
	private $failedVisitor = [
		'fields' => [
			'name'			=> '',
			'surname'		=> '',
			'nick'			=> '',
			'email'			=> '',
			'birthday'		=> '',
			'street'		=> '',
			'city'			=> '',
			'postal_code'	=> '',
			'group_num'		=> '',
			'group_name'	=> '',
			'troop_name'	=> '',
			'arrival'		=> '',
			'departure'		=> '',
			'comment'		=> '',
			'question'		=> '',
			'question2'		=> '',
		],
		'options' => [
			'province'		=> '',
			'fry_dinner'	=> '',
			'sat_breakfast'	=> '',
			'sat_lunch'		=> '',
			'sat_dinner'	=> '',
			'sun_breakfast'	=> '',
			'sun_lunch'		=> '',
		],
	];

	private $successRegistrationUri;

	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests
	public function ensure_that_registration_works(\AcceptanceTester $I)
	{
		$I->wantTo('ensure that registration works');
		$I->amOnPage('srazvs/registration/');
		$I->see('Registrace na srazy VS');
	}

	public function it_should_fail_registrate_new_visitor(AcceptanceTester $I)
	{
		$I->amOnPage('/srazvs/registration/');
		$I->see('Registrace na srazy VS');
		$I->wantTo('Fail registration of new visitor');
		$I->click('Uložit', '#registration');
		$I->seeInCurrentUrl('/srazvs/registration/');
//		$I->see('Jméno musí být vyplněno (max 20 znaků)!');
		/* TODO:
			- test fail messages
			- controlling data without javascript needed
		*/
	}

	public function it_should_registrate_new_visitor(AcceptanceTester $I)
	{
		$I->amOnPage('/srazvs/registration/');
		$I->see('Registrace na srazy VS');
		$I->wantTo('Registrate new visitor');
		foreach($this->successVisitor['fields'] as $field => $value) {
			$I->fillField($field, $value);
		}
		foreach($this->successVisitor['options'] as $option => $value) {
			$I->selectOption($option, $value);
		}
		$I->click('Uložit', '#registration');
		$this->successRegistrationUri = $I->grabFromCurrentUrl();
		$I->seeCurrentUrlMatches('~/srazvs/registration/\?hash=[a-z0-9]*&error=\d+&cms=check~');
		$I->see('Údaje byly úspěšně nahrány!');
		$I->see('Registrace na srazy K + K');
		foreach ($this->successVisitor['fields'] as $field => $value) {
			$I->see($value);
		}
		$I->see($this->successVisitor['options']['province']);
	}

	public function visitor_should_edit_its_registration(AcceptanceTester $I)
	{
		$I->amOnPage($this->successRegistrationUri);
		$I->wantTo('Edit registration by visitor');
		$I->click('Upravit', '#button-line');
		foreach ($this->successVisitor['fields'] as $field => $value) {
			$I->seeInField($field, $value);
		}
		foreach ($this->successVisitor['options'] as $option => $value) {
			$I->seeOptionIsSelected($option, $value);
		}
		$I->fillField('name', 'Metro');
		$I->click('Uložit', '#registration');
		$I->seeCurrentUrlMatches('~/srazvs/registration/\?hash=[a-z0-9]*&error=ok&cms=check~');
		$I->see('Údaje byly úspěšně nahrány!');
		$I->see('Registrace na srazy K + K');
		$I->see('Metro');
		$I->dontSee('robo', '#name');
	}

	public function visitor_should_change_its_programs(AcceptanceTester $I)
	{
		$I->wantTo('Change programs by visitor');
	}

}
