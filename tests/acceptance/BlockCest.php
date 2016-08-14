<?php


class BlockCest
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
	public function ensure_that_blocks_works(\AcceptanceTester $I)
	{
		$I->wantTo('Ensure that blocks works');
		$I->amOnPage('/srazvs/block/');
		$I->see('Správa bloků');
	}

	public function ensure_that_creating_works(\AcceptanceTester $I)
	{
		$I->wantTo('Ensure that blocks creating works');
		$I->amOnPage('/srazvs/block/');
		$I->see('Správa bloků');
		$I->click('NOVÝ BLOK');
		$I->see('nový blok');
		$I->see('Název:');
		$I->see('Den:');
		$I->see('Od:');
		$I->see('Do:');
		$I->see('Popis:');
		$I->see('Lektor:');
		$I->see('E-mail:');
		$I->see('Programový blok:');
		$I->see('Nezobrazovat programy:');
		$I->see('Kapacita:');
		$I->see('Kategorie:');
	}
}
