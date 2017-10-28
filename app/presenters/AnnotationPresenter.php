<?php

namespace App\Presenters;

use App\Components\Forms\AnnotationForm;
use App\Components\Forms\Factories\IAnnotationFormFactory;
use App\Models\MeetingModel;
use App\Services\AnnotationService;
use Tracy\Debugger;
use \Exception;

class AnnotationPresenter extends BasePresenter
{

	/**
	 * @var IAnnotationFormFactory
	 */
	private $annotationFormFactory;

	/**
	 * @var AnnotationService
	 */
	private $annotationService;

	/**
	 * @var MeetingModel
	 */
	private $meetingModel;

	/**
	 * @param AnnotationService $service
	 * @param MeetingModel $model
	 */
	public function __construct(AnnotationService $service, MeetingModel $model)
	{
		$this->setAnnotationService($service);
		$this->setMeetingModel($model);
	}

	/**
	 * @param  IAnnotationFormFactory $factory
	 */
	public function injectAnnotationFormFactory(IAnnotationFormFactory $factory)
	{
		$this->annotationFormFactory = $factory;
	}

	/**
	 * @param  string $id
	 * @param  string $type
	 */
	public function renderEdit(string $guid, string $type)
	{
		$template = $this->getTemplate();

		$annotation = $this->getAnnotationService()->findByType($guid, $type);

		$template->guid = $guid;
		$template->annotation = $annotation;
		$template->meeting = $this->getMeetingModel()->find($this->getMeetingId());
		$template->program = $this->getAnnotationService()->findParentProgram($annotation);

		$this['annotationForm']->setDefaults($annotation->toArray());
	}

	/**
	 * @return AnnotationFormControl
	 */
	protected function createComponentAnnotationForm(): AnnotationForm
	{
		$control = $this->annotationFormFactory->create();
		$control->setMeetingId($this->getMeetingId());
		$type = $this->getParameter('type');
		$control->onAnnotationSave[] = function(AnnotationForm $control, $annotation) use ($type) {
			try {
				$result = $this->getAnnotationService()->updateByType($type, $annotation);

				Debugger::log('Modification of annotation id ' . $annotation->guid . ' with data ' . json_encode($annotation) . ' successfull, result: ' . json_encode($result), Debugger::INFO);

				$this->flashMessage('Položka byla úspěšně upravena', 'ok');
			} catch(Exception $e) {
				Debugger::log('Modification of annotation id ' . $annotation->guid . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);

				$this->flashMessage('Modification of annotation id ' . $annotation->guid . ' failed, result: ' . $e->getMessage(), 'error');
			}
		};

		return $control;
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

	/**
	 * @return MeetingModel
	 */
	public function getMeetingModel(): MeetingModel
	{
		return $this->meetingModel;
	}

	/**
	 * @param MeetingModel $meetingModel
	 *
	 * @return self
	 */
	public function setMeetingModel(MeetingModel $meetingModel): self
	{
		$this->meetingModel = $meetingModel;

		return $this;
	}

}