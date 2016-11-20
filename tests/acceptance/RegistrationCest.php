<?php

/**
 * Registration must be public
 */

class RegistrationCest extends CestCase
{

	private $successVisitor = [
		'fields' => [
			'name'			=> 'robo',
			'surname'		=> 'Černík',
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

	private $successRegistrationUri;
	private $succeededRegistrationUrl = '~/srazvs/registration/check/[a-z0-9]*?error=\d+|ok~';
	private $updatedRegistrationUrl = '~/srazvs/registration/update/[a-z0-9]*~';

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
		$I->see('Třebíč - podzim 2015');
	}

	/**
	 * @skipTest
	 */
	public function it_should_fail_registrate_new_visitor(AcceptanceTester $I)
	{
		$I->amOnPage('/srazvs/registration/');
		$I->see('Registrace na srazy VS');
		$I->wantTo('Fail registration of new visitor');
		$I->click('Uložit', '#registration');
		$I->seeInCurrentUrl('/srazvs/registration/');
		//$I->see('Jméno musí být vyplněno (max 20 znaků)!');
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
		$this->fillForm($I, $this->successVisitor);
		$I->click('Uložit', '#registration');
		$this->successRegistrationUri = $I->grabFromCurrentUrl();
		$I->seeCurrentUrlMatches($this->succeededRegistrationUrl);
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
		$this->seeInForm($I, $this->successVisitor);
		$I->fillField('name', 'Metro');
		$I->click('Uložit', '#registration');
		$this->successRegistrationUri = $I->grabFromCurrentUrl();
		$I->seeCurrentUrlMatches($this->updatedRegistrationUrl);
		//$I->see('Údaje byly úspěšně nahrány!');
		//$I->see('Registrace na srazy K + K');
		//$I->see('Metro');
		//$I->dontSee('robo', '#name');
	}

	public function visitor_should_change_its_programs(AcceptanceTester $I)
	{
		$this->successRegistrationUri = str_replace('update', 'check', $this->successRegistrationUri);
		$I->amOnPage($this->successRegistrationUri);
		$I->wantTo('Change programs by visitor');
		$I->click('Upravit', '#button-line');
		$I->seeOptionIsSelected('blck_6', '0');
		$I->dontSeeOptionIsSelected('blck_6', '1');
		$I->selectOption('blck_6', '1');
		$I->click('Uložit', '#registration');
		$I->seeCurrentUrlMatches($this->updatedRegistrationUrl);
		//$I->see('Údaje byly úspěšně nahrány!');
		//$I->see('Registrace na srazy K + K');
		//$I->see('- Hry a hříčky');
	}

}
