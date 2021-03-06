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

	public function ensure_that_programs_works(\AcceptanceTester $I)
	{
		$I = $this->logIn($I);
		$I->wantTo('ensure that programs works');
		$I->amOnPage('/srazvs/program/');
		$I->see('Správa programů');
	}

	public function ensure_that_visitors_works(\AcceptanceTester $I)
	{
		$I = $this->logIn($I);
		$I->wantTo('ensure that visitors works');
		$I->amOnPage('/srazvs/visitor/');
		$I->see('Správa účastníků');
	}

	public function ensure_that_exports_works(\AcceptanceTester $I)
	{
		$I = $this->logIn($I);
		$I->wantTo('ensure that exports works');
		$I->amOnPage('/srazvs/export/');
		$I->see('Exporty');
	}

	public function ensure_that_categories_works(\AcceptanceTester $I)
	{
		$I = $this->logIn($I);
		$I->wantTo('ensure that categories works');
		$I->amOnPage('/srazvs/category/');
		$I->see('Správa kategorií');
	}

	public function ensure_that_settings_works(\AcceptanceTester $I)
	{
		$I = $this->logIn($I);
		$I->wantTo('ensure that settings works');
		$I->amOnPage('/srazvs/settings/');
		$I->see('Nastavení systému');
	}

	public function it_should_get_error_404_on_nonexisting_page(\AcceptanceTester $I)
	{
		$I->amOnPage('/srazvs/registration/www.qrka.cz');
		$I->seeResponseCodeIs(404);
		$I->amOnPage('/srazvs/program/www.qrka.cz');
		$I->seeResponseCodeIs(404);
	}

}
