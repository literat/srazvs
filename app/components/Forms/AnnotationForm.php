<?php

namespace App\Components\Forms;

use App\Services\AnnotationService;
use App\Models\MeetingModel;
use Nette\Application\UI\Form;

class AnnotationForm extends BaseForm
{

	const TEMPLATE_NAME = 'AnnotationForm';

	const MESSAGE_REQUIRED = 'Hodnota musí být vyplněna!';
	const MESSAGE_MAX_LENGTH = '%label nesmí mít více jak %d znaků!';

	/**
	 * @var Closure
	 */
	public $onAnnotationSave;

	/**
	 * @var AnnotationService
	 */
	protected $annotationService;

	/**
	 * @var  MeetingModel
	 */
	protected $meetingModel;

	/**
	 * @param ProvinceModel $model
	 */
	public function __construct(
		AnnotationService $annotation,
		MeetingModel $meeting
	) {
		$this->setAnnotationService($annotation);
		$this->setMeetingModel($meeting);
	}

	/**
	 * @return void
	 */
	public function render()
	{
		$template = $this->getTemplate();
		$template->setFile($this->buildTemplatePath());
		$template->render();
	}

	/**
	 * @param  array $defaults
	 * @return AnnotationForm
	 */
	public function setDefaults(array $defaults = []): AnnotationForm
	{
		$this['annotationForm']->setDefaults($defaults);

		return $this;
	}

	/**
	 * @return Form
	 */
	public function createComponentAnnotationForm(): Form
	{
		$form = new Form;

		$form->addText('name', 'Název:')
			->setRequired(static::MESSAGE_REQUIRED)
			->addRule(Form::MAX_LENGTH, static::MESSAGE_MAX_LENGTH, 30)
			->getLabelPrototype()->setAttribute('class', 'required');
		$form->addTextArea('description', 'Popis:')
			->setAttribute('placeholder', 'Doplň, prosím, popis Tvého programu (bude se zobrazovat účastníkům na webu při výběru programu).');
		$form->addTextArea('material', 'Materiál:')
			->setAttribute('placeholder', 'Doplň, prosím, vybavení, které budeš potřebovat na Tvůj program a které máme zajistit.');
		$form->addText('tutor', 'Lektor:');
		$form->addEmail('email', 'E-mail:');
		$form->addText('capacity', 'Kapacita:');

		$form->addHidden('guid');

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
		$annotation = $form->getValues();

		$this->onAnnotationSave($this, $annotation);
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
	 * @return self
	 */
	protected function setMeetingModel(MeetingModel $model): self
	{
		$this->meetingModel = $model;

		return $this;
	}

	/**
	 * @return AnnotationService
	 */
	public function getAnnotationService(): AnnotationService
	{
		return $this->annotationService;
	}

	/**
	 * @param AnnotationService $annotationService
	 *
	 * @return self
	 */
	public function setAnnotationService(AnnotationService $annotationService): self
	{
		$this->annotationService = $annotationService;

		return $this;
	}

}
