<?php

namespace App\Components\Forms;

use App\Repositories\ProgramRepository;
use App\Models\ProvinceModel;
use App\Models\BlockModel;
use App\Models\MealModel;
use App\Models\MeetingModel;
use Nette\Application\UI\Form;
use App\Services\SkautIS\UserService;
use Nette\Utils\ArrayHash;

class VisitorForm extends BaseForm
{

	const TEMPLATE_NAME = 'VisitorForm';

	const MESSAGE_REQUIRED = 'Hodnota musí být vyplněna!';
	const MESSAGE_MAX_LENGTH = '%label nesmí mít více jak %d znaků!';

	/**
	 * @var Closure
	 */
	public $onVisitorSave;

	/**
	 * @var ProvinceModel
	 */
	protected $provinceModel;

	/**
	 * @var ProgramRepository
	 */
	protected $programRepository;

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
     * VisitorForm constructor.
     * @param ProvinceModel     $province
     * @param ProgramRepository $program
     * @param BlockModel        $block
     * @param MeetingModel      $meeting
     */
	public function __construct(
		ProvinceModel $province,
		ProgramRepository $program,
		BlockModel $block,
		MeetingModel $meeting
	) {
		$this->setProvinceModel($province);
		$this->setProgramRepository($program);
		$this->setBlockModel($block);
		$this->setMeetingModel($meeting);
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
	 * @param  array|ArrayHash $defaults
	 * @return VisitorForm
	 */
	public function setDefaults($defaults): VisitorForm
	{
		$this['visitorForm']->setDefaults($defaults);

		return $this;
	}

	/**
	 * @return Form
	 */
	public function createComponentVisitorForm(): Form
	{
		$provinces = $this->getProvinceModel()->all();

		$form = new Form;

		$form->addText('name', 'Jméno:', 30)
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 20)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('surname', 'Příjmení:', 30)
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 30)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('nick', 'Přezdívka:', 30)
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 20)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addEmail('email', 'E-mail:')
			->setRequired(static::MESSAGE_REQUIRED)
            ->setAttribute('size', 30)
			->getLabelPrototype()->setAttribute('class', 'required');
        $form->addTbDatePicker('birthday', 'Datum narození:', null, 16)
            ->setRequired(static::MESSAGE_REQUIRED)
            ->setFormat('d.m.Y')
            ->setAttribute('placeholder', 'dd. mm. rrrr')
            ->setAttribute('id', 'birthday')
            ->setAttribute('class', 'datePicker')
            ->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('street', 'Ulice:', 30)
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 30)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('city', 'Město:', 30)
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 64)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('postal_code', 'PSČ:', 30)
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::PATTERN, 'Číslo musí být ve formátu nnnnn!', '[1-9]{1}[0-9]{4}')
			->setAttribute('placeholder', '12345')
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('group_num', 'Číslo středika/přístavu:', 30)
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::PATTERN, 'Číslo musí být ve formátu nnn.nn!', '[1-9]{1}[0-9a-zA-Z]{2}\.[0-9a-zA-Z]{1}[0-9a-zA-Z]{1}')
			->setAttribute('placeholder', '214.02')
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('group_name', 'Název střediska/přístavu:', 30)
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 50)
			->setAttribute('placeholder', '2. přístav Poutníci Kolín')
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addText('troop_name', 'Název oddílu:', 30)
			->setAttribute('placeholder', '22. oddíl Galeje')
			->addCondition(Form::FILLED, true)
				->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 50);

		$form->addSelect('province', 'Kraj:', $provinces)
			->setPrompt('zvolte kraj');

		$form = $this->buildMealSwitcher($form);

		$form->addTextArea('arrival', 'Informace o příjezdu:', 50, 3)
			->setAttribute('placeholder', 'Napište, prosím, stručně jakým dopravním prostředkem a v kolik hodin (přibližně) přijedete na místo srazu.');
		$form->addTextArea('departure', 'Informace o odjezdu:', 50, 3)
			->setAttribute('placeholder', 'Napište, prosím, stručně jakým dopravním prostředkem a v kolik hodin (přibližně) sraz opustíte.');
		$form->addTextArea('comment', 'Dotazy, přání, připomínky, stížnosti:', 50, 8);
		$form->addTextArea('question', 'Vaše nabídka:', 50, 8)
			->setAttribute('placeholder', 'Vaše nabídka na sdílení dobré praxe (co u vás umíte dobře a jste ochotni se o to podělit)');
		$form->addTextArea('question2', 'Počet a typy lodí:', 50, 8)
			->setAttribute('placeholder', 'Počet a typy lodí, které sebou přivezete (vyplňte pokud ano)');
        $form->addText('bill', 'Zaplaceno:', 30)
            ->setDefaultValue(0);
        $form->addText('cost', 'Poplatek:', 30)
            ->setDefaultValue($this->getMeetingModel()->getPrice('cost'));

		$form = $this->buildProgramSwitcher($form);

		$form->addHidden('mid', $this->getMeetingId());
		$form->addHidden('backlink');

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
		$visitor = $form->getValues();
		$visitor['meeting'] = $this->getMeetingId();

		$this->onVisitorSave($this, $visitor);
	}

	/**
	 * @param  Form   $form
	 * @return Form
	 */
	protected function buildProgramSwitcher(Form $form): Form
	{
		$programBlocks = $this->fetchProgramBlocks();

		foreach ($programBlocks as $block) {

			$programsInBlock = $this->getProgramRepository()->findByBlockId($block->id);

			$programs = [
				0 => 'Nebudu přítomen'
			];

			foreach ($programsInBlock as $program) {
				$programs[$program->id] = $program->name;
			}

			$form->addRadioList(
				'blck_' . $block->id,
				$block->day . ', ' . $block->from .' - ' . $block->to .' : ' . $block->name,
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
			$form->addSelect($name, $label . ':', $yesNoArray)
                ->setAttribute('style', 'width:195px; font-size:11px;');
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
	 * @return VisitorFormFactory
	 */
	protected function setProvinceModel(ProvinceModel $model): VisitorForm
	{
		$this->provinceModel = $model;

		return $this;
	}

	/**
	 * @return ProgramRepository
	 */
	protected function getProgramRepository(): ProgramRepository
	{
		return $this->programRepository;
	}

	/**
	 * @param  ProgramRepository $repository
	 * @return VisitorFormFactory
	 */
	protected function setProgramRepository(ProgramRepository $repository): VisitorForm
	{
		$this->programRepository = $repository;

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
	 * @return VisitorForm
	 */
	protected function setBlockModel(BlockModel $model): VisitorForm
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
	 * @return VisitorForm
	 */
	protected function setMeetingModel(MeetingModel $model): VisitorForm
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
	 * @return VisitorForm
	 */
	protected function setMealField(string $meal): VisitorForm
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
	 * @return VisitorForm
	 */
	protected function setProgramField(string $program): VisitorForm
	{
		$this->programFields[] = $program;

		return $this;
	}

	/**
	 * @return VisitorForm
	 */
	protected function setProgramFields(): VisitorForm
	{
		$programBlocks = $this->fetchProgramBlocks();

		foreach ($programBlocks as $block) {
			$programFieldName = 'blck_' . $block->id;
			$this->setProgramField($programFieldName);
		}

		return $this;
	}

	/**
	 * @return  VisitorForm
	 */
	protected function setMealFields(): VisitorForm
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
					$visitorsOnProgram = $this->getProgramRepository()->countVisitors($id);
					$programCapacity = $this->getProgramRepository()->find($id)->capacity;

					return $visitorsOnProgram >= $programCapacity;
				}
			}, ARRAY_FILTER_USE_BOTH)
		);
	}

}
