<?php

class BlockCest extends CestCase
{

	private $simpleBlock = [
		'fields' => [
			'name'			=> 'testing block',
		],
		'options' => [
			'day'			=> 'sobota',
			'start_hour'	=> '10',
			'end_hour'		=> '12',
			'start_minute'	=> '15',
			'end_minute'	=> '30',
		],
	];

	private $fullBlock = [
		'fields' => [
			'name'			=> 'testing block',
			"//textarea[@name='description']"	=> 'testování',
			'tutor'			=> 'Tester Testovič',
			'email'			=> 'tester@testovic.local',
			'capacity'		=> 1,
		],
		'options' => [
			'day'			=> 'sobota',
			'start_hour'	=> '10',
			'end_hour'		=> '12',
			'start_minute'	=> '15',
			'end_minute'	=> '30',
			'program'		=> 1,
			'display_progs'	=> 0,
			'category'		=> 1,
		],
	];

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

	public function it_should_create_simple_block(\AcceptanceTester $I)
	{
		$I->amOnPage('/srazvs/block/');
		$I->see('Správa bloků');
		$I->wantTo('Create new block with basic data');
		$I->click('NOVÝ BLOK', '#content');
		$I->seeCurrentUrlMatches('~/srazvs/block/\?cms=new~');
		$this->fillForm($I, $this->simpleBlock);
		$I->click('Uložit', '#content');
		$I->seeInCurrentUrl('/srazvs/?error=ok');
	}

	public function it_should_update_simple_block(\AcceptanceTester $I)
	{
	}

	public function it_should_create_full_block(\AcceptanceTester $I)
	{
		$I->amOnPage('/srazvs/block/');
		$I->see('Správa bloků');
		$I->wantTo('Create new block with all data');
		$I->click('NOVÝ BLOK', '#content');
		$I->seeCurrentUrlMatches('~/srazvs/block/\?cms=new~');
		$this->fillForm($I, $this->fullBlock);
		$I->click('Uložit', '#content');
		$I->seeInCurrentUrl('/srazvs/?error=ok');
	}

	public function it_should_update_full_block(\AcceptanceTester $I)
	{
	}

}
