<?php

namespace App\Components\Forms;

use App\Components\BaseControl;
use App\Models\ProvinceModel;
use App\Models\ProgramModel;
use App\Models\BlockModel;
use App\Models\MealModel;
use App\Models\MeetingModel;
use Nette\Application\UI\Form;

class RegistrationForm extends BaseForm
{

	const TEMPLATE_NAME = 'RegistrationForm';

	const MESSAGE_REQUIRED = 'Hodnota musí být vyplněna!';
	const MESSAGE_MAX_LENGTH = '%label nesmí mít více jak %d znaků!';

	/**
	 * @var Closure
	 */
	public $onRegistrationSave;

	/**
	 * @var ProvinceModel
	 */
	protected $provinceModel;

	/**
	 * @var ProgramModel
	 */
	protected $programModel;

	/**
	 * @var BlockModel
	 */
	protected $blockModel;

	/**
	 * @var  MeetingModel
	 */
	protected $meetingModel;

	/**
	 * @param ProvinceModel $model
	 */
	public function __construct(
		ProvinceModel $province,
		ProgramModel $program,
		BlockModel $block,
		MeetingModel $meeting
	) {
		$this->setProvinceModel($province);
		$this->setProgramModel($program);
		$this->setBlockModel($block);
		$this->setMeetingModel($meeting);
	}

	/**
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
	 * @return Form
	 */
	public function createComponentRegistrationForm(): Form
	{
		$provinces = $this->getProvinceModel()->all();

		$form = new Form;

		$renderer = $form->getRenderer();
		$renderer->wrappers['label']['container'] = 'td';

		$form->addText('name', 'Jméno:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 20);
		$form->addText('surname', 'Příjmení:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 30);
		$form->addText('nick', 'Přezdívka:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 20);
		$form->addEmail('email', 'E-mail:')
			->setRequired(static::MESSAGE_REQUIRED);
		$form->addDatePicker('birthday', 'Datum narození:', 16)
			->setRequired(static::MESSAGE_REQUIRED)
			->setReadOnly(false)
			->setAttribute('placeholder', 'dd.mm.rrrr');
		$form->addText('street', 'Ulice:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 30);;
		$form->addText('city', 'Město:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 64);;
		$form->addText('postal_code', 'PSČ:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::PATTERN, 'Číslo musí být ve formátu nnnnn!', '[1-9]{1}[0-9]{4}')
			->setAttribute('placeholder', '12345');
		$form->addText('group_num', 'Číslo středika/přístavu:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::PATTERN, 'Číslo musí být ve formátu nnn.nn!', '[1-9]{1}[0-9a-zA-Z]{2}\.[0-9a-zA-Z]{1}[0-9a-zA-Z]{1}')
			->setAttribute('placeholder', '214.02');
		$form->addText('group_name', 'Název střediska/přístavu:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 50)
			->setAttribute('placeholder', '2. přístav Poutníci Kolín');
		$form->addText('troop_name', 'Název oddílu:')
			->setAttribute('placeholder', '22. oddíl Galeje')
			->addCondition(Form::FILLED, true)
				->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 50);

		$form->addSelect('province', 'Kraj:', $provinces)
			->setPrompt('Zvolte kraj');

		$form = $this->buildMealSwitcher($form);

		$form->addTextArea('arrival', 'Informace o příjezdu:')
			->setAttribute('placeholder', 'Napište, prosím, stručně jakým dopravním prostředkem a v kolik hodin (přibližně) přijedete na místo srazu.');
		$form->addTextArea('departure', 'Informace o odjezdu:')
			->setAttribute('placeholder', 'Napište, prosím, stručně jakým dopravním prostředkem a v kolik hodin (přibližně) sraz opustíte.');
		$form->addTextArea('comment', 'Dotazy, přání, připomínky, stížnosti:');
		$form->addTextArea('question', 'Vaše nabídka:')
			->setAttribute('placeholder', 'Vaše nabídka na sdílení dobré praxe (co u vás umíte dobře a jste ochotni se o to podělit)');
		$form->addTextArea('question2', 'Počet a typy lodí:')
			->setAttribute('placeholder', 'Počet a typy lodí, které sebou přivezete (vyplňte pokud ano)');

		$form = $this->buildProgramSwitcher($form);

		$form->addHidden('mid', $this->getMeetingId());
		$form->addHidden('bill', 0);
		$form->addHidden('cost', $this->getMeetingModel()->getPrice('cost'));

		$form->addSubmit('save', 'Uložit');
		$form->addSubmit('reset', 'Storno');

		$form->onSuccess[] = [$this, 'processForm'];

		return $form;
	}

	/**
	 * @param  Form $form
	 * @return void
	 */
	public function processForm(Form $form)
	{
		$registration = $form->getValues();
		$registration['meeting'] = $this->getMeetingId();

		$this->onRegistrationSave($this, $registration);
	}

	/**
	 * @param  Form   $form
	 * @return Form
	 */
	protected function buildProgramSwitcher(Form $form): Form
	{
		$programBlocks = $this->getBlockModel()->getProgramBlocks($this->getMeetingId());

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
	 * @return RegistrationForm
	 */
	protected function setBlockModel(BlockModel $model): RegistrationForm
	{
		$this->blockModel = $model;

		return $this;
	}

	/**
	 * @return MeetingModel
	 */
	protected function getMeetingModel(): MeetingModel
	{
		return $this->meetingModel;
	}

	/**
	 * @param  MeetingModel $model
	 * @return RegistrationForm
	 */
	protected function setMeetingModel(MeetingModel $model): RegistrationForm
	{
		$this->meetingModel = $model;

		return $this;
	}

}
