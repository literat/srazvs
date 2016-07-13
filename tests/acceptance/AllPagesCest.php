<?php

class AllPagesCest
{

	private $cookie = null;

	public function logIn(\AcceptanceTester $I)
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
		//$this->cookie = $I->grabCookie('vodni.skauting.local');

		return $I;
	}

	// tests
	public function ensure_that_frontpage_works(\AcceptanceTester $I)
	{
		$I = $this->logIn($I);
		$I->wantTo('ensure that frontpage works');
		//$I->setCookie( 'vodni.skauting.local', $this->cookie );
		$I->amOnPage('srazvs/');
		$I->see('Srazy VS');
	}

}
