<?php

class MeetingCest extends CestCase
{

	private $simpleMeeting = [
		'fields' => [
			'place'			=> 'Testovací - simple',
			'start_date'	=> '2016-10-13',
			'end_date'		=> '2016-10-14',
			'open_reg'		=> '2016-10-13 00:00:00',
			'close_reg'		=> '2016-10-14 00:00:00',
		],
	];

	private $fullMeeting = [
		'fields' => [
			'place'			=> 'Testovací - full',
			'start_date'	=> '2016-10-13',
			'end_date'		=> '2016-10-14',
			'open_reg'		=> '2016-10-13 00:00:00',
			'close_reg'		=> '2016-10-14 00:00:00',
			'cost'			=> 500,
			'advance'		=> 300,
			'numbering'		=> 'x2016',
			'contact'		=> 'Tester',
			'email'			=> 'test@tester.local',
			'gsm'			=> '123456789',
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
	public function ensure_that_meeting_works(\AcceptanceTester $I)
	{
		$I->wantTo('ensure that meeting works');
		$I->amOnPage('/srazvs/meeting/');
		$I->see('Správa srazů');
	}

	public function ensure_that_meeting_listing_works(\AcceptanceTester $I)
	{
		$I->wantTo('ensure that meeting listing works');
		$I->amOnPage('/srazvs/meeting/');
		$I->see('Správa srazů');
		$I->click('seznam srazů');
		$I->seeInCurrentUrl('/srazvs/meeting/?cms=list-view');
		$I->see('Seznam srazů');
	}

	public function ensure_that_meeting_creating_works(\AcceptanceTester $I)
	{
		$I->wantTo('ensure that meeting creating works');
		$I->amOnPage('/srazvs/meeting/');
		$I->see('Správa srazů');
		$I->click('seznam srazů');
		$I->seeInCurrentUrl('/srazvs/meeting/?cms=list-view');
		$I->see('Seznam srazů');
		$I->click('NOVÝ SRAZ', '#content');
		$I->seeInCurrentUrl('/srazvs/meeting/?cms=new&page=meeting');
		$I->see('úprava srazu');
	}

	public function it_should_create_simple_meeting(\AcceptanceTester $I)
	{
		$I->amOnPage('/srazvs/meeting/?cms=list-view');
		$I->see('Správa srazů');
		$I->wantTo('Create new meeting with basic data');
		$I->click('NOVÝ SRAZ', '#content');
		$I->seeInCurrentUrl('/srazvs/meeting/?cms=new&page=meeting');
		$this->fillForm($I, $this->simpleMeeting);
		$I->click('Uložit', '#content');
		$I->seeInCurrentUrl('/srazvs/meeting?error=ok');
	}

	public function it_should_create_full_meeting(\AcceptanceTester $I)
	{
		$I->amOnPage('/srazvs/meeting/?cms=list-view');
		$I->see('Správa srazů');
		$I->wantTo('Create new meeting with all data');
		$I->click('NOVÝ SRAZ', '#content');
		$I->seeInCurrentUrl('/srazvs/meeting/?cms=new&page=meeting');
		$this->fillForm($I, $this->fullMeeting);
		$I->click('Uložit', '#content');
		$I->seeInCurrentUrl('/srazvs/meeting?error=ok');
	}

}
