<?php


class ExportCest extends CestCase
{
	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests
	public function it_should_export_evidence_confirm_for_all_visitors(AcceptanceTester $I)
	{
		$I->wantTo('Export evidence cofirmation for all visitors');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('potvrzení o přijetí zálohy');
		$I->seeInCurrentUrl('/srazvs/export/evidence/confirm');
		$I->see('POTVRZENÍ O PŘIJETÍ ZÁLOHY');
		$I->see('Přijato od:');
		$I->see('Účel platby:');
		$I->see('Celkem Kč:');
		$I->see('Slovy Kč:');
	}

	public function it_should_export_evidence_for_all_visitors(AcceptanceTester $I)
	{
		$I->wantTo('Export evidence for all visitors');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('příjmový pokladní doklad');
		$I->seeInCurrentUrl('/srazvs/export/evidence/evidence');
		$I->see('PŘÍJMOVÝ POKLADNÍ DOKLAD');
		$I->see('Přijato od:');
		$I->see('Účel platby:');
		$I->see('Celkem Kč:');
		$I->see('Slovy Kč:');
		$I->see('Převzal:');
	}

	public function it_should_export_evidence_summary(AcceptanceTester $I)
	{
		$I->wantTo('Export evidence summary');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('kompletní příjmový pokladní doklad');
		$I->seeInCurrentUrl('/srazvs/export/evidence/summary');
		$I->see('Příjmení a Jméno');
		$I->see('Narození');
		$I->see('Adresa');
		$I->see('Jednotka');
		$I->see('Záloha');
		$I->see('Doplatek');
		$I->see('Podpis');
	}

	public function it_should_export_public_program(AcceptanceTester $I)
	{
		$I->wantTo('Export public program');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('veřejný program');
		$I->seeInCurrentUrl('/srazvs/export/program-public');
		$I->see('program srazu vodních skautů');
		$I->see('pátek');
		$I->see('sobota');
		$I->see('neděle');
		$I->see('DEBUG_MODE');
	}

	public function it_should_export_publically_public_program(AcceptanceTester $I)
	{
		$I->wantTo('Export publically public program');
		$I->amOnPage('/srazvs/program/public');
		$I->seeInCurrentUrl('/srazvs/program/public');
		$I->click('Stáhněte si program srazu ve formátu PDF', '#content-pad-program');
		$I->seeInCurrentUrl('/srazvs/export/program-public');
		$I->see('program srazu vodních skautů');
		$I->see('pátek');
		$I->see('sobota');
		$I->see('neděle');
		$I->see('DEBUG_MODE');
	}

	public function it_should_export_private_program(AcceptanceTester $I)
	{
		$I->wantTo('Export private program');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('osobní program');
		$I->seeInCurrentUrl('/srazvs/export/program-cards');
		$I->see('SRAZ VS');
		$I->see('pátek');
		$I->see('sobota');
		//$I->see('neděle');
		$I->see('DEBUG_MODE');
	}

	public function it_should_export_meeting_shedule_in_big_format(AcceptanceTester $I)
	{
		$I->wantTo('Export meeting schedule in big format');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('program srazu - velký formát');
		$I->seeInCurrentUrl('/srazvs/export/program-large');
		$I->see('program srazu vodních skautů');
		$I->see('pátek');
		$I->see('sobota');
		$I->see('neděle');
		$I->see('DEBUG_MODE');
	}

	public function it_should_export_meeting_shedule_into_badge(AcceptanceTester $I)
	{
		$I->wantTo('Export meeting schedule into visitor`s badge');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('program srazu - do visačky');
		$I->seeInCurrentUrl('/srazvs/export/program-badge');
		$I->see('pátek');
		$I->see('sobota');
		$I->see('neděle');
		$I->see('DEBUG_MODE');
	}

	public function it_should_export_name_badges(AcceptanceTester $I)
	{
		$I->wantTo('Export name badges');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('jmenovky');
		$I->seeInCurrentUrl('/srazvs/export/name-badges');
		$I->see('roboTester');
		$I->see('DEBUG_MODE');
	}

	public function it_should_export_attendance_list(AcceptanceTester $I)
	{
		$I->wantTo('Export attendance list');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('prezenční listina');
		$I->seeInCurrentUrl('/srazvs/export/attendance');
		$I->see('Příjmení a Jméno');
		$I->see('Narození');
		$I->see('Adresa');
		$I->see('Středisko/Přístav');
		$I->see('Podpis');
		$I->see('DEBUG_MODE');
	}

	public function it_should_export_name_list(AcceptanceTester $I)
	{
		$I->wantTo('Export name list');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('jmenný seznam');
		$I->seeInCurrentUrl('/srazvs/export/name-list');
		$I->see('Příjmení, Jméno, Přezdívka');
		$I->see('Adresa');
		$I->see('Středisko/Přístav');
		$I->see('DEBUG_MODE');
	}

	public function it_should_export_meal_tickets(AcceptanceTester $I)
	{
		$I->wantTo('Export meal tickets');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('stravenky');
		$I->seeInCurrentUrl('/srazvs/export/meal-ticket');
		$I->see('Pátek');
		$I->see('Sobota');
		$I->see('Neděle');
		$I->see('Snídaně');
		$I->see('Oběd');
		$I->see('Večeře');
		$I->see('DEBUG_MODE');
	}

	public function it_should_export_program_details(AcceptanceTester $I)
	{
		$I->wantTo('Export program details');
		$I = $this->logIn($I);
		$I->amOnPage('/srazvs/export/?mid=1');
		$I->seeInCurrentUrl('/srazvs/export/');
		$I->click('detaily programů');
		$I->seeInCurrentUrl('/srazvs/export/program-details');
		$I->see('Program:');
		$I->see('Popis:');
		$I->see('Lektor:');
		$I->see('E-mail:');
		$I->see('DEBUG_MODE');
	}

	public function it_should_export_publically_program_details(AcceptanceTester $I)
	{
		$I->wantTo('Export publically program details');
		$I->amOnPage('/srazvs/program/public?mid=1');
		$I->seeInCurrentUrl('/srazvs/program/public');
		$I->click('Stáhněte si detaily programů srazu ve formátu PDF', '#content-pad-program');
		$I->seeInCurrentUrl('/srazvs/export/program-details');
		$I->see('Program:');
		$I->see('Popis:');
		$I->see('Lektor:');
		$I->see('E-mail:');
		$I->see('DEBUG_MODE');
	}

}
