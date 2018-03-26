<?php

namespace App\Components\Forms;

use App\Models\ProvinceModel;
use App\Repositories\ProgramRepository;
use App\Models\BlockModel;
use App\Models\MeetingModel;
use Nette\Application\UI\Form;
use App\Services\SkautIS\UserService;

class RegistrationForm extends VisitorForm
{

	const TEMPLATE_NAME = 'RegistrationForm';

	/**
	 * @var Closure
	 */
	public $onRegistrationSave;

	/**
	 * @var UserService
	 */
	protected $userService;

	public function __construct(
		ProvinceModel $province,
		ProgramRepository $program,
		BlockModel $block,
		MeetingModel $meeting,
		UserService $user
	) {
		$this->setProvinceModel($province);
		$this->setProgramRepository($program);
		$this->setBlockModel($block);
		$this->setMeetingModel($meeting);
		$this->setUserService($user);
	}

	/**
	 * @param  array $defaults
	 * @return self
	 */
	public function setDefaults($defaults): BaseForm
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
			->addRule(
				Form::PATTERN,
				'Číslo musí být ve formátu nnn.nn!',
				'[1-9]{1}[0-9a-zA-Z]{2}\.[0-9a-zA-Z]{1}[0-9a-zA-Z]{1}'
			)
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
			->setPrompt('zvolte kraj');

		$form = $this->buildMealSwitcher($form);

		$form->addTextArea('arrival', 'Informace o příjezdu:')
			->setAttribute(
				'placeholder',
				'Napište, prosím, stručně jakým dopravním prostředkem a v kolik hodin (přibližně) přijedete na místo srazu.'
			);
		$form->addTextArea('departure', 'Informace o odjezdu:')
			->setAttribute(
				'placeholder',
				'Napište, prosím, stručně jakým dopravním prostředkem a v kolik hodin (přibližně) sraz opustíte.'
			);
		$form->addTextArea('comment', 'Dotazy, přání, připomínky, stížnosti:');
		$form->addTextArea('question', 'Vaše nabídka:')
			->setAttribute(
				'placeholder',
				'Vaše nabídka na sdílení dobré praxe (co u vás umíte dobře a jste ochotni se o to podělit)'
			);
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

}
