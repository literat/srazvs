<?php

namespace App\Forms;

use App\Components\BaseControl;
use App\Models\ProvinceModel;
use App\Models\ProgramModel;
use App\Models\BlockModel;
use App\Models\MealModel;
use Nette\Application\UI\Form;

class RegistrationForm extends BaseControl
{

	const TEMPLATE_NAME = 'RegistrationForm';

	public $onRegistrationSave;

	protected $meetingId = null;

	protected $provinceModel;

	protected $programModel;

	protected $blockModel;

	/**
	 * @param ProvinceModel $model
	 */
	public function __construct(ProvinceModel $province, ProgramModel $program, BlockModel $block, $meetingId = null)
	{
		$this->setProvinceModel($province);
		$this->setProgramModel($program);
		$this->setBlockModel($block);
		$this->meetingId = $meetingId;
	}

	/**
	 * @param  string $mealColumn
	 * @param  string $mealName
	 * @return void
	 */
	public function render()
	{
		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->imgDir = IMG_DIR;
		$template->wwwDir = HTTP_DIR;
		$template->render();
	}

	/**
	 * @param  string $mealColumn
	 * @param  string $mealName
	 * @return void
	 */
	public function createComponentRegistrationForm()
	{

		$provinces = $this->getProvinceModel()->all();

		$form = new Form;
		$form->addText('name', 'Jméno:')->setRequired()
			->addRule(Form::MIN_LENGTH, 'Jméno musí mít alespoň %d znak', 1)
			->addRule(Form::MAX_LENGTH, 'Jméno nesmí mít více jak %d znaků', 20);
		$form->addText('surname', 'Příjmení:')->setRequired()
			->addRule(Form::MIN_LENGTH, 'Příjmení musí mít alespoň %d znak', 1)
			->addRule(Form::MAX_LENGTH, 'Příjmení nesmí mít více jak %d znaků', 30);
		$form->addText('nick', 'Přezdívka:')->setRequired()
			->addRule(Form::MIN_LENGTH, 'Přezdívka musí mít alespoň %d znak', 1)
			->addRule(Form::MAX_LENGTH, 'Přezdívka nesmí mít více jak %d znaků', 20);
		$form->addEmail('email', 'E-mail:')->setRequired();
		$form->addText('birthday', 'Datum narození:')->setRequired();
		$form->addText('street', 'Ulice:')->setRequired();
		$form->addText('city', 'Město:')->setRequired();
		$form->addText('postal_code', 'PSČ:')->setRequired();
		$form->addText('group_num', 'Číslo středika/přístavu:')->setRequired();
		$form->addText('group_name', 'Název střediska/přístavu:')->setRequired();
		$form->addText('troop_name', 'Název oddílu:');

		$form->addSelect('province', 'Kraj:', $provinces);

		$form = $this->buildMealSwitcher($form);

		$form->addTextArea('arrival', 'Informace o příjezdu:');
		$form->addTextArea('departure', 'Informace o odjezdu:');
		$form->addTextArea('comment', 'Dotazy, přání, připomínky, stížnosti:');
		$form->addTextArea('question', 'Vaše nabídka:');
		$form->addTextArea('question2', 'Počet a typy lodí:');

		$form = $this->buildProgramSwitcher($form);

		$form->addHidden('mid', $this->meetingId);
		$form->addHidden('bill', 0);
		$form->addHidden('cost', '$cost');

		$form->addSubmit('save', 'Uložit');
		$form->addSubmit('reset', 'Storno');

		$form->onSuccess[] = [$this, 'processForm'];

		return $form;
	}

	public function processForm($form)
	{
		dd($form->getValues());
		// mohu použít $this->database
		// zpracování formuláře, např. změním údaje upravované kategorie
		// $category je nějaký řádek tabulky (entita), kterou zpracováváme
		$this->onRegistrationSave($this, $registration);
	}

	/**
	 * @param  Form   $form
	 * @return Form
	 */
	protected function buildProgramSwitcher(Form $form): Form
	{
		$programBlocks = $this->getBlockModel()->getProgramBlocks($this->meetingId);

		foreach ($programBlocks as $block) {

			$programsInBlock = $this->getProgramModel()->findByBlockId($block->id);

			$programs = [
				0 => 'Nebudu přítomen'
			];

			foreach ($programsInBlock as $program) {
				$programs[$program->id] = $program->name;
			}

			$form->addRadioList('blck_' . $block->id, $block->day . ', ' . $block->from .' - ' . $block->to .' : ' . $block->name, $programs)
				->setDefaultValue(0);
		}

		return $form;
	}

	/**
	 * @param  Form   $form
	 * @return Form
	 */
	protected function buildMealSwitcher(Form $form): Form
	{
		$yesNoArray = [
			'ne'  => 'ne',
			'ano' => 'ano',
		];

		foreach (MealModel::$meals as $name => $label) {
			$form->addSelect($name, $label . ':', $yesNoArray);
		}

		return $form;
	}

	/**
	 * @return ProvinceModel
	 */
	protected function getProvinceModel(): ProvinceModel
	{
		return $this->provinceModel;
	}

	/**
	 * @param  ProvinceModel $model
	 * @return RegistrationFormFactory
	 */
	protected function setProvinceModel(ProvinceModel $model): RegistrationForm
	{
		$this->provinceModel = $model;

		return $this;
	}

	/**
	 * @return ProgramModel
	 */
	protected function getProgramModel(): ProgramModel
	{
		return $this->programModel;
	}

	/**
	 * @param  ProgramModel $model
	 * @return RegistrationFormFactory
	 */
	protected function setProgramModel(ProgramModel $model): RegistrationForm
	{
		$this->programModel = $model;

		return $this;
	}

	/**
	 * @return BlockModel
	 */
	protected function getBlockModel(): BlockModel
	{
		return $this->blockModel;
	}

	/**
	 * @param  BlockModel $model
	 * @return RegistrationFormFactory
	 */
	protected function setBlockModel(BlockModel $model): RegistrationForm
	{
		$this->blockModel = $model;

		return $this;
	}

}
