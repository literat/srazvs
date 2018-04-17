<?php

namespace App\Components\Forms;

use App\Repositories\BlockRepository;
use App\Repositories\CategoryRepository;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\SubmitButton;

class ProgramForm extends BaseForm
{
	const TEMPLATE_NAME = 'ProgramForm';

	const MESSAGE_REQUIRED = 'Hodnota musí být vyplněna!';
	const MESSAGE_MAX_LENGTH = '%label nesmí mít více jak %d znaků!';

	/**
	 * @var callable[]
	 */
	public $onProgramSave = [];

	/**
	 * @var callable[]
	 */
	public $onProgramReset = [];

	/**
	 * @var BlockRepository
	 */
	protected $blockRepository;

	/**
	 * @var CategoryRepository
	 */
	protected $categoryRepository;

	public function __construct(
		BlockRepository $blockRepository,
		CategoryRepository $categoryRepository
	) {
		$this->setBlockRepository($blockRepository);
		$this->setCategoryRepository($categoryRepository);
	}

	/**
	 * @return void
	 */
	public function render()
	{
		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->backlink = 'Program:listing';
		$template->render();
	}

	public function setDefaults(ActiveRow $defaults): ProgramForm
	{
		$this['programForm']->setDefaults($defaults);

		return $this;
	}

	/**
	 * @return Form
	 */
	public function createComponentProgramForm(): Form
	{
		$form = new Form;

		$form->addText('name', 'Název:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 50)
			->setAttribute('size', 50)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addTextArea('description', 'Popis:')
			->setAttribute('rows', 10)
			->setAttribute('cols', 80);
		$form->addTextArea('material', 'Materiál:')
			->setAttribute('rows', 2)
			->setAttribute('cols', 80);
		$form->addText('tutor', 'Lektor:')
			->setAttribute('size', 30);
		$form->addText('email', 'E-mail:')
			->setAttribute('size', 30);
		$form->addText('capacity', 'Kapacita:')
			->setDefaultValue(0)
			->setAttribute('size', 10)
			->setAttribute('placeholder', 0);
		$form->addRadioList('display_in_reg', 'Zobrazit v registraci:', [1 => 'Ano', 0 => 'Ne'])
			->getSeparatorPrototype()->setName(null);
		$form->addSelect('block', 'Blok:', $this->buildBlockSelect());
		$form->addSelect('category', 'Kategorie:', $this->buildCategorySelect());

		$form->addHidden('id');
		$form->addHidden('guid');
		$form->addHidden('backlink');

		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'btn-primary')
			->onClick[] = [$this, 'processForm'];
		$form->addSubmit('reset', 'Storno')
			->setAttribute('class', 'btn-reset')
			->onClick[] = [$this, 'processReset'];

		$form->setDefaults(['display_in_reg' => 1]);

		$form = $this->setupRendering($form);

		return $form;
	}

	/**
	 * @param  SubmitButton $button
	 * @return void
	 */
	public function processForm(SubmitButton $button)
	{
		$program = $button->getForm()->getValues();

		//$this->onProgramSave($this, $program);
		call_user_func([$this, 'onProgramSave', $program]);
	}

	/**
	 * @param  SubmitButton $button
	 * @return void
	 */
	public function processReset(SubmitButton $button)
	{
		$program = $button->getForm()->getValues();

		//$this->onProgramReset($this, $program);
		call_user_func([$this, 'onProgramReset', $program]);
	}

	/**
	 * @return array
	 */
	protected function buildBlockSelect(): array
	{
		$blocks = $this->getBlockRepository()->findByMeeting($this->getMeetingId());

		$selectContent = [];

		foreach ($blocks as $block) {
			$selectContent[$block->id] = sprintf(
				'%s, %s - %s : %s',
				$block->day,
				$block->from->format('%H:%I:%S'),
				$block->to->format('%H:%I:%S'),
				$block->name
			);
		}

		return $selectContent;
	}

	/**
	 * @return array
	 */
	protected function buildCategorySelect(): array
	{
		$categories = $this->getCategoryRepository()->findAll();

		$selectContent = [];

		foreach ($categories as $category) {
			$selectContent[$category->id] = $category->name;
		}

		return $selectContent;
	}

	/**
	 * @return BlockRepository
	 */
	public function getBlockRepository(): BlockRepository
	{
		return $this->blockRepository;
	}

	/**
	 * @param BlockRepository $blockRepository
	 *
	 * @return self
	 */
	public function setBlockRepository(BlockRepository $repository): self
	{
		$this->blockRepository = $repository;

		return $this;
	}

	/**
	 * @return CategoryRepository
	 */
	public function getCategoryRepository(): CategoryRepository
	{
		return $this->categoryRepository;
	}

	/**
	 * @param CategoryRepository $categoryRepository
	 *
	 * @return self
	 */
	public function setCategoryRepository(CategoryRepository $repository): self
	{
		$this->categoryRepository = $repository;

		return $this;
	}
}
