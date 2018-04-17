<?php

namespace App\Components\Forms;

use App\Entities\BlockEntity;
use App\Repositories\BlockRepository;
use App\Repositories\CategoryRepository;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;

class BlockForm extends BaseForm
{
	const TEMPLATE_NAME = 'BlockForm';

	const MESSAGE_REQUIRED = 'Hodnota musí být vyplněna!';
	const MESSAGE_MAX_LENGTH = '%label nesmí mít více jak %d znaků!';

	/**
	 * @var callable[]
	 */
	public $onBlockSave = [];

	/**
	 * @var callable[]
	 */
	public $onBlockReset = [];

	/**
	 * @var BlockRepository
	 */
	protected $blockRepository;

	/**
	 * @var CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * @var array
	 */
	protected $days = [
		'pátek'  => 'pátek',
		'sobota' => 'sobota',
		'neděle' => 'neděle',
	];

	/**
	 * @var array
	 */
	protected $hours = [
		0 => "00","01","02","03","04","05","06","07","08","09",
			 "10","11","12","13","14","15","16","17","18","19",
			 "20","21","22","23"
	];

	/**
	 * @var array
	 */
	protected $minutes = [
		00 => "00",
		05 => "05",
		10 => "10",
		15 => "15",
		20 => "20",
		25 => "25",
		30 => "30",
		35 => "35",
		40 => "40",
		45 => "45",
		50 => "50",
		55 => "55",
	];

	public function __construct(
		BlockRepository $blockRepository,
		CategoryRepository $categoryRepository
	) {
		$this->setBlockRepository($blockRepository);
		$this->setCategoryRepository($categoryRepository);
	}

	public function render()
	{
		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->backlink = 'Block:listing';
		$template->render();
	}

	/**
	 * @param  BlockEntity $defaults
	 * @return self
	 */
	public function setDefaults(BlockEntity $defaults): self
	{
		$defaults = $this->guardDefaults($defaults);

		$this['blockForm']->setDefaults($defaults->toArray());

		return $this;
	}

	/**
	 * @return Form
	 */
	public function createComponentBlockForm(): Form
	{
		$form = new Form;

		$form->addText('name', 'Název:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 50)
			->setAttribute('size', 50)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addSelect('day', 'Den:', $this->days)
			->setRequired();
		$form->addSelect('start_hour', null, $this->hours)
			->setDefaultValue(date('G'));
		$form->addSelect('start_minute', null, $this->minutes);
		$form->addSelect('end_hour', null, $this->hours)
			->setDefaultValue(date('G', strtotime('+1 hour')));
		$form->addSelect('end_minute', null, $this->minutes);
		$form->addTextArea('description', 'Popis:')
			->setAttribute('rows', 10)
			->setAttribute('cols', 80);
		$form->addText('tutor', 'Lektor:')
			->setAttribute('size', 30);
		$form->addText('email', 'E-mail:')
			->setAttribute('size', 30);
		$form->addText('capacity', 'Kapacita:')
			->setDefaultValue(0)
			->setAttribute('size', 10)
			->setAttribute('placeholder', 0);
		$form->addCheckbox('program')
			->setDefaultValue(1);
		$form->addCheckbox('display_progs')
			->setDefaultValue(0);
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

		$form = $this->setupRendering($form);

		return $form;
	}

	/**
	 * @param  SubmitButton $button
	 * @return void
	 */
	public function processForm(SubmitButton $button)
	{
		$block = $button->getForm()->getValues();

		//$this->onBlockSave($this, $block);
		call_user_func([$this, 'onBlockSave', $block]);
	}

	/**
	 * @param  SubmitButton $button
	 * @return void
	 */
	public function processReset(SubmitButton $button)
	{
		$block = $button->getForm()->getValues();

		//$this->onBlockReset($this, $block);
		call_user_func([$this, 'onBlockReset', $block]);
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
	 * @param  BlockEntity $defaults
	 * @return BlockEntity
	 */
	protected function guardDefaults(BlockEntity $defaults): BlockEntity
	{
		if (!array_key_exists($defaults->category, $this->buildCategorySelect())) {
			$defaults->category = 0;
		}

		return $defaults;
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
