<?php

namespace App\Presenters;

use App\Components\Forms\AnnotationForm;
use App\Components\Forms\Factories\IAnnotationFormFactory;
use App\Models\MeetingModel;
use App\Services\AnnotationService;

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
	 * @param MeetingModel      $model
	 */
	public function __construct(AnnotationService $service, MeetingModel $model)
	{
		$this->setAnnotationService($service);
		$this->setMeetingModel($model);
	}

	/**
	 * @param IAnnotationFormFactory $factory
	 */
	public function injectAnnotationFormFactory(IAnnotationFormFactory $factory)
	{
		$this->annotationFormFactory = $factory;
	}

	/**
	 * @param string $guid
	 * @param string $type
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
	 * @return AnnotationForm
	 */
	protected function createComponentAnnotationForm(): AnnotationForm
	{
		$control = $this->annotationFormFactory->create();
		$control->setMeetingId($this->getMeetingId());
		$type = $this->getParameter('type');
		$control->onAnnotationSave[] = function ($annotation) use ($type) {
			try {
				$result = $this->getAnnotationService()->updateByType($type, $annotation);

				$this->logInfo(
					'Modification of annotation id %s with data %s successfull, result: %s',
					[
						$annotation->guid,
						json_encode($annotation),
						json_encode($result),
					]
				);

				$this->flashSuccess('Položka byla úspěšně upravena');
			} catch (\Exception $e) {
				$this->logError("Modification of annotation id {$annotation->guid} failed, result: {$e->getMessage()}");

				$this->flashError("Modification of annotation id {$annotation->guid} failed, result: {$e->getMessage()}");
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
