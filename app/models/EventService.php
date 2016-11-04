<?php

/**
 * User service
 */
class EventService extends BaseService
{

	/**
	 * Returns Role ID of logged user
	 *
	 * @param   void
	 * @return  type
	 */
	public function insertParticipant($personId, $educationEventId, $educationEventCourseId)
	{
		return $this->skautis->event->participantEducationInsert(([
			'ID_EventEducation' => $educationEventId,
			'ID_Person' => $personId,
			'ID_EventEducationCourse' => $educationEventCourseId,
		]));
	}

}