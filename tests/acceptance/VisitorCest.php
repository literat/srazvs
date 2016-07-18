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
		$I->click('#visitor-1 .checker');
		$I->see('Položka byla zkontrolována!', '.error');
		$I->seeInCurrentUrl('/srazvs/visitor/?error=checked');
		$I->seeElement('.checked');
	}

	public function i_should_uncheck_visitor(AcceptanceTester $I)
	{
		$I->wantTo('Uncheck visitor');
		$I->amOnPage('/srazvs/visitor/?mid=1');
		$I->seeInCurrentUrl('/srazvs/visitor/');
		$I->click('#visitor-1 .checker');
		$I->see('Položce byl odebrán příznak!', '.error');
		$I->seeInCurrentUrl('/srazvs/visitor/?error=unchecked');
		$I->canSeeElement('.checked');
	}

}
