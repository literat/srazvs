<?php


class ExportCest
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
	public function it_should_export_evidence_confirm_for_all_visitors(AcceptanceTester $I)
	{
		$I->wantTo('Export evidence cofirmation for all visitors');
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('potvrzení o přijetí zálohy');
		$I->seeInCurrentUrl('/srazvs/export/?evidence=confirm');
		$I->see('POTVRZENÍ O PŘIJETÍ ZÁLOHY');
		$I->see('Přijato od:');
		$I->see('Účel platby:');
		$I->see('Celkem Kč:');
		$I->see('Slovy Kč:');
	}

	public function it_should_export_evidence_for_all_visitors(AcceptanceTester $I)
	{
		$I->wantTo('Export evidence for all visitors');
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('příjmový pokladní doklad');
		$I->seeInCurrentUrl('/srazvs/export/?evidence=evidence');
		$I->see('PŘÍJMOVÝ POKLADNÍ DOKLAD');
		$I->see('Přijato od:');
		$I->see('Účel platby:');
		$I->see('Celkem Kč:');
		$I->see('Slovy Kč:');
		$I->see('Převzal:');
	}

	public function it_should_export_public_program(AcceptanceTester $I)
	{
		$I->wantTo('Export public program');
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('veřejný program');
		$I->seeInCurrentUrl('/srazvs/export/?program-public&cms=public');
		$I->see('program srazu vodních skautů');
		$I->see('pátek');
		$I->see('sobota');
		$I->see('neděle');
		$I->see('DEBUG_MODE');
	}

	public function it_should_export_private_program(AcceptanceTester $I)
	{
		$I->wantTo('Export private program');
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('osobní program');
		$I->seeInCurrentUrl('/srazvs/export/?program-cards');
		$I->see('SRAZ VS');
		$I->see('pátek');
		$I->see('sobota');
		//$I->see('neděle');
		$I->see('DEBUG_MODE');
	}

	public function it_should_export_meeting_shedule_in_big_format(AcceptanceTester $I)
	{
		$I->wantTo('Export meeting schedule in big format');
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('program srazu - velký formát');
		$I->seeInCurrentUrl('/srazvs/export/?program-large');
		$I->see('program srazu vodních skautů');
		$I->see('pátek');
		$I->see('sobota');
		$I->see('neděle');
		$I->see('DEBUG_MODE');
	}
}
