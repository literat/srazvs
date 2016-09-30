<?php
/**
 * Export Controller
 *
 * This file handles the retrieval and serving of exports
 */
class ExportController extends BaseController
{
	/**
	 * This template variable will hold the 'view' portion of our MVC for this
	 * controller
	 */
	public $template = 'exports';

	private $container;
	private $export;
	private $latte;
	private $program;

	public function __construct($database, $container)
	{
		$this->database = $database;
		$this->container = $container;
		$this->router = $this->container->parameters['router'];
		$this->export = $this->container->createServiceExports();
		$this->program = $this->container->createServiceProgram();
		$this->latte = $this->container->getService('latte');
		$this->templateDir = 'exports';
	}

	/**
	 * This is the default function that will be called by router.php
	 *
	 * @param 	array 	$getVars 	the GET variables posted to index.php
	 */
	public function init()
	{
		// program detail and program public must be public
		if(!$this->router->getParameter('program-public') && !$this->router->getParameter('program-details')) {
			include_once(INC_DIR.'access.inc.php');
		}

		if($mid = $this->requested('mid', '')){
			$_SESSION['meetingID'] = $mid;
		} else {
			$mid = $_SESSION['meetingID'];
		}

		$this->export->setMeetingId($mid);
		$this->program->setMeetingId($mid);

		switch($this->router->getParameter('action')){
			case 'attendance':
				$this->export->printAttendance();
				break;
			case 'evidence':
				//if(!empty($this->requested('vid'))) {
				if($this->requested('vid')) {
					$this->export->printEvidence($this->requested('type'), intval($this->requested('vid')));
				} else {
					$this->export->printEvidence($this->requested('type'));
				}
				break;
			case 'visitorExcel':
				$this->export->printVisitorsExcel();
				break;
			case 'mealTicket':
				$this->export->printMealTicket();
				break;
			case 'nameBadges':
				$names =$this->requested('names', '');
				$this->export->printNameBadges($names);
				break;
			case 'programDetails':
				$this->export->printProgramDetails();
				break;
			case 'programCards':
				$this->export->printProgramCards();
				break;
			case 'programLarge':
				$this->export->printLargeProgram();
				break;
			case 'programBadge':
				$this->export->printProgramBadges();
				break;
			case 'programPublic':
				$this->export->printPublicProgram();
				break;
			case 'nameList':
				$this->export->printNameList();
				break;
		}

		$parameters = [
			'cssDir'	=> CSS_DIR,
			'jsDir'		=> JS_DIR,
			'imgDir'	=> IMG_DIR,
			'wwwDir'	=> HTTP_DIR,
			'user'		=> $this->getUser($_SESSION[SESSION_PREFIX.'user']),
			'meeting'	=> $this->getPlaceAndYear($_SESSION['meetingID']),
			'menu'		=> $this->generateMenu(),
			'graph'		=> $this->export->renderGraph(),
			'graphHeight'	=> $this->export->getGraphHeight(),
			'account'	=> $this->export->getMoney('account'),
			'balance'	=> $this->export->getMoney('balance'),
			'suma'		=> $this->export->getMoney('suma'),
			'programs'	=> $this->program->renderExportPrograms(),
			'materials'	=> $this->export->getMaterial(),
			'meals'		=> $this->export->renderMealCount(),
		];

		$this->latte->render(__DIR__ . '/../templates/' . $this->templateDir.'/'.$this->template . '.latte', $parameters);
	}

	/**
	 * převod desitkového čísla vyjádřeného matematicky na číslo vyjádřené slovně
	 * param int $number - číslo v desítkové soustavě (od 0 do 999999)
	 * param bool $zero - true:když bude $number 0 zobrazí se na výstupu nula; false:když bude $number 0 zobrazí se na výstupu (bool)false
	 * return string nebo bool false
	 * (C) Martin Bumba, http://mbumba.cz
	 */
	public static function number2word($number, $zero = false)
	{
		$units = ['', 'jedna','dva','tři','čtyři','pět','šest','sedm','osm','devět'];
		$between = [
			11 => 'jedenáct',
			12 => 'dvanáct',
			13 => 'třináct',
			14 => 'čtrnáct',
			15 => 'patnáct',
			16 => 'šestnáct',
			17 => 'sedmnáct',
			18 => 'osmnáct',
			19 => 'devatenáct'
		];
		$dozens = [
			'',
			'deset',
			'dvacet',
			'třicet',
			'čtyřicet',
			'padesát',
			'šedesát',
			'sedmdesát',
			'osmdesát',
			'devadesát'
		];
		$number = (string) ltrim(round($number), 0);
		$length = strlen($number);

		// treatment of 0
		if($number == 0) return $zero ? 'nula':false;
		// 1 regulation - units
		elseif($length == 1) return $units[$number];
		// 2 regulations - desítky
		elseif($length == 2) {
			$dozensAndUnits = $number{0} . $number{1};
			if($dozensAndUnits == 10) echo "deset";
			elseif($dozensAndUnits < 20) {
				return $between[$dozensAndUnits];
			}
			else {
				return $dozens[$number{0}] . $units[$number{1}];
			}
		}
		// 3 regulations - hundreds
		elseif($length == 3) {
			if($number{0} == 1) return "sto" . self::number2word(substr($number, 1));
			elseif($number{0} == 2) return "dvěstě" . self::number2word(substr($number, 1));
			elseif($number{0} == 3 || $number{0} == 4) return $units[$number{0}] . "sta" . self::number2word(substr($number, 1));
			else return $units[$number{0}] . "set" . self::number2word(substr($number, 1));
		}
		// 4 regulations - thousands
		elseif($length == 4) {
			if($number{0} == 1) return "tisíc" . self::number2word(substr($number, 1));
			elseif($number{0} < 5) return $units[$number{0}] . "tisíce" . self::number2word(substr($number, 1));
			else return $units[$number{0}] . "tisíc" . self::number2word(substr($number, 1));
		}
		// 5 regulations - tens of thousands
		elseif($length == 5) {
			$tensOfThousands = $number{0} . $number{1};
			if($tensOfThousands == 10) return "desettisíc" . self::number2word(substr($number, 2));
			elseif($tensOfThousands < 20) return $between[$tensOfThousands] . "tisíc" . self::number2word(substr($number, 2));
			elseif($tensOfThousands < 100) return $dozens[$number{0}] . $units[$number{1}] . "tisíc" . self::number2word(substr($number, 2));
		}
		// 6 regulations - hundreds of thousands
		elseif($length == 6) {
			if($number{0} == 1) {
				if($number{1} . $number{2} == 00) return "stotisíc" . self::number2word(substr($number, 3));
				else return "sto" . self::number2word(substr($number, 1));
			}
			elseif($number{0} == 2) return "dvěstě" . self::number2word(substr($number, 1));
			elseif($number{0} == 3 || $number{0} == 4) return $units[$number{0}] . "sta" . self::number2word(substr($number, 1));
			else return $units[$number{0}] . "set" . self::number2word(substr($number, 1));
		}

		return false;
	}
}
