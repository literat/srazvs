<?php

namespace App\Components\Forms;

use DateTime;
use App\Components\BaseControl;
use App\Models\ProvinceModel;
use App\Models\ProgramModel;
use App\Models\BlockModel;
use App\Models\MealModel;
use App\Models\MeetingModel;
use Nette\Application\UI\Form;
use Nette\Forms\Controls;
use App\Services\SkautIS\UserService;

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
	 * @var array
	 */
	protected $mealFields = [];

	/**
	 * @var array
	 */
	protected $programFields = [];

	/**
	 * @var UserService
	 */
	protected $userService;

	/**
	 * @param ProvinceModel $model
	 */
	public function __construct(
		ProvinceModel $province,
		ProgramModel $program,
		BlockModel $block,
		MeetingModel $meeting,
		UserService $user
	) {
		$this->setProvinceModel($province);
		$this->setProgramModel($program);
		$this->setBlockModel($block);
		$this->setMeetingModel($meeting);
		$this->setUserService($user);
	}

	/**
	 * @return void
	 */
	public function render()
	{
		$this->setMealFields();
		$this->setProgramFields();

		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->meals = $this->getMealFields();
		$template->programs = $this->getProgramFields();
		$template->render();
	}

	/**
	 * @param  array $defaults
	 * @return RegistrationForm
	 */
	public function setDefaults(array $defaults = []): RegistrationForm
	{
		$this['registrationForm']->setDefaults($defaults);

		return $this;
	}

	/**
	 * @return Form
	 */
	public function createComponentRegistrationForm(): Form
	{
		$provinces = $this->getProvinceModel()->all();

		$form = new Form;

		$form->addText('name', 'Jméno:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 20)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('surname', 'Příjmení:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 30)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('nick', 'Přezdívka:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 20)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addEmail('email', 'E-mail:')
			->setRequired(static::MESSAGE_REQUIRED)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addTbDatePicker('birthday', 'Datum narození:', null, 16)
			->setRequired(static::MESSAGE_REQUIRED)
			->setFormat('d. m. Y')
			->setAttribute('placeholder', 'dd. mm. rrrr')
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('street', 'Ulice:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 30)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('city', 'Město:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 64)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('postal_code', 'PSČ:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::PATTERN, 'Číslo musí být ve formátu nnnnn!', '[1-9]{1}[0-9]{4}')
			->setAttribute('placeholder', '12345')
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('group_num', 'Číslo středika/přístavu:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::PATTERN, 'Číslo musí být ve formátu nnn.nn!', '[1-9]{1}[0-9a-zA-Z]{2}\.[0-9a-zA-Z]{1}[0-9a-zA-Z]{1}')
			->setAttribute('placeholder', '214.02')
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('group_name', 'Název střediska/přístavu:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 50)
			->setAttribute('placeholder', '2. přístav Poutníci Kolín')
			->getLabelPrototype()->setAttribute('class', 'required');
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

		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'btn-primary');
		$form->addSubmit('reset', 'storno')
			->setAttribute('class', 'btn-reset');

		$form = $this->setupRendering($form);

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
	protected function setupRendering(Form $form): Form
	{
		// setup form rendering
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

		// make form and controls compatible with Twitter Bootstrap
		$form->getElementPrototype()->class('form-horizontal');
		foreach ($form->getControls() as $control) {
			if ($control instanceof Controls\Button) {
				$control->getControlPrototype()
					->addClass(empty($usedPrimary) ? 'btn btn-default' : '');
				$usedPrimary = TRUE;
			} elseif (
				$control instanceof Controls\TextBase ||
				$control instanceof Controls\SelectBox ||
				$control instanceof Controls\MultiSelectBox
			) {
				$control->getControlPrototype()
					->addClass('form-control');
			} elseif (
				$control instanceof Controls\Checkbox ||
				$control instanceof Controls\CheckboxList ||
				$control instanceof Controls\RadioList
			) {
				$control->getSeparatorPrototype()
					->setName('div')
					->addClass($control->getControlPrototype()->type);
			}
		}

		return $form;
	}

	/**
	 * @param  Form   $form
	 * @return Form
	 */
	protected function buildProgramSwitcher(Form $form): Form
	{
		$programBlocks = $this->fetchProgramBlocks();

		foreach ($programBlocks as $block) {

			$programsInBlock = $this->getProgramModel()->findByBlockId($block->id);

			$programs = [
				0 => 'Nebudu přítomen'
			];

			foreach ($programsInBlock as $program) {
				$programs[$program->id] = $program->name;
			}

			$form->addRadioList(
				'blck_' . $block->id, $block->day . ', ' . $block->from .' - ' . $block->to .' : ' . $block->name,
				$programs
			)->setDefaultValue(0)
			->setDisabled($this->filterFilledCapacity($programs));
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

		foreach ($this->fetchMeals() as $name => $label) {
			$this->setMealField($name);
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

	/**
	 * @return array
	 */
	protected function getMealFields(): array
	{
		return $this->mealFields;
	}

	/**
	 * @param  string $meal
	 * @return RegistrationForm
	 */
	protected function setMealField(string $meal): RegistrationForm
	{
		if(!in_array($meal, $this->mealFields)) {
			$this->mealFields[] = $meal;
		}

		return $this;
	}

	/**
	 * @return array
	 */
	protected function getProgramFields(): array
	{
		return $this->programFields;
	}

	/**
	 * @param  string $program
	 * @return RegistrationForm
	 */
	protected function setProgramField(string $program): RegistrationForm
	{
		$this->programFields[] = $program;

		return $this;
	}

	/**
	 * @return RegistrationForm
	 */
	protected function setProgramFields(): RegistrationForm
	{
		$programBlocks = $this->fetchProgramBlocks();

		foreach ($programBlocks as $block) {
			$programFieldName = 'blck_' . $block->id;
			$this->setProgramField($programFieldName);
		}

		return $this;
	}

	/**
	 * @return  RegistrationForm
	 */
	protected function setMealFields(): RegistrationForm
	{
		$meals = $this->fetchMeals();

		foreach ($meals as $name => $label) {
			$this->setMealField($name);
		}

		return $this;
	}

	/**
	 * @return Row
	 */
	protected function fetchProgramBlocks()
	{
		return $this->getBlockModel()->getProgramBlocks($this->getMeetingId());
	}

	/**
	 * @return array
	 */
	protected function fetchMeals()
	{
		return MealModel::$meals;
	}

	/**
	 * @return UserService
	 */
	protected function getUserService()
	{
		return $this->userService;
	}

	/**
	 * @param  UserService $service
	 * @return $this
	 */
	protected function setUserService(UserService $service)
	{
		$this->userService = $service;

		return $this;
	}

	/**
	 * @param  array  $programs
	 * @return array
	 */
	protected function filterFilledCapacity(array $programs = []): array
	{
		return array_keys(
			array_filter($programs, function($name, $id) {
				if ($id) {
					$visitorsOnProgram = $this->getProgramModel()->countProgramVisitors($id);
					$programCapacity = $this->getProgramModel()->findByProgramId($id)->capacity;

					return $visitorsOnProgram >= $programCapacity;
				}
			}, ARRAY_FILTER_USE_BOTH)
		);
	}

}
