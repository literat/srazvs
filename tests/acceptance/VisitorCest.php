<?php

class VisitorCest
{

	public function _before(AcceptanceTester $I)
	{
		$I->wantTo('login to admin');
		$I->amOnPage('admin/');
		$I->seeInCurrentUrl('admin/');
		$I->fillField('username','tester');
		$I->fillField('password','tester');
		$I->checkOption('persistent');
		$I->click('Přihlásit', '#content');
		$I->see('Úvod');
		$I->see('tester');
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests
	public function i_should_check_visitor(AcceptanceTester $I)
	{
		$I->wantTo('Check visitor');
		$I->amOnPage('/srazvs/visitor/?mid=1');
		$I->seeInCurrentUrl('/srazvs/visitor/');
		$I->click('#visitor-10 .checker');
		//$I->seeInCurrentUrl('/srazvs/visitor/check/10');
		$I->see('Položka byla úspěšně zkontrolována', '.flash');
		$I->seeInCurrentUrl('/srazvs/visitor');
		$I->seeElement('.checked');
	}

	public function i_should_uncheck_visitor(AcceptanceTester $I)
	{
		$I->wantTo('Uncheck visitor');
		$I->amOnPage('/srazvs/visitor/?mid=1');
		$I->seeInCurrentUrl('/srazvs/visitor/');
		$I->click('#visitor-10 .checker');
		//$I->seeInCurrentUrl('/srazvs/visitor/uncheck/10');
		$I->see('Položka byla nastavena jako nekontrolována', '.flash');
		$I->seeInCurrentUrl('/srazvs/visitor');
		$I->canSeeElement('.checked');
	}

}
