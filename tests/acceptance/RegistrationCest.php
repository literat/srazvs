<?php

class RegistrationCest
{

	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests
	public function it_should_registrate_new_visitor(AcceptanceTester $I)
	{
		$I->amOnPage('/srazvs/registration/');
		$I->see('Registrace na srazy VS');
		$I->wantTo('Registrate new visitor');
		$I->fillField('name','Robo');
		$I->fillField('surname','Tester');
		$I->fillField('nick','robo-tester');
		$I->fillField('email','robo@tester.local');
		$I->fillField('birthday', date('d.m.Y'));
		$I->fillField('street','Testovací 987');
		$I->fillField('city','Testákov 6');
		$I->fillField('postal_code','12345');
		$I->fillField('group_num','1A2.34');
		$I->fillField('group_name','Testeři');
		$I->fillField('troop_name','Klikači');
		$I->selectOption('province', 'Hlavní město Praha');
		$I->selectOption('fry_dinner', 'ano');
		$I->selectOption('sat_breakfast', 'ano');
		$I->selectOption('sat_lunch', 'ano');
		$I->selectOption('sat_dinner', 'ano');
		$I->selectOption('sun_breakfast', 'ano');
		$I->selectOption('sun_lunch', 'ano');
		$I->fillField('arrival','Někdy');
		$I->fillField('departure','jindy');
		$I->fillField('comment','než');
		$I->fillField('question','právě');
		$I->fillField('question2','teď!');
		$I->click('Uložit', '#registration');
		$I->seeCurrentUrlMatches('~/srazvs/registration/\?hash=[a-z0-9]*&error=\d+&cms=check~');
		$I->see('Registrace na srazy K + K');
		$I->see('Robo');
		$I->see('Tester');
		$I->see('robo-tester');
		$I->see('robo@tester.local');
		$I->see(date('d.m.Y'));
		$I->see('Testovací 987');
		$I->see('Testákov 6');
		$I->see('12345');
		$I->see('1A2.34');
		$I->see('Testeři');
		$I->see('Klikači');
		$I->see('Hlavní město Praha');
		$I->see('Někdy');
		$I->see('jindy');
		$I->see('než');
		$I->see('právě');
		$I->see('teď!');
	}

	public function it_should_fail_registrate_new_visitor(AcceptanceTester $I)
	{
		$I->wantTo('Fail registration of new visitor');
	}

	public function visitor_should_edit_its_registration(AcceptanceTester $I)
	{
		$I->wantTo('Edit registration by visitor');
	}

	public function visitor_should_change_its_programs(AcceptanceTester $I)
	{
		$I->wantTo('Change programs by visitor');
	}

}
