<?php

namespace App\Services\SkautIS;

/**
 * User service
 */
class EventService extends SkautisService
{

	/**
	 * Returns Role ID of logged user
	 *
	 * @param   void
	 * @return  type
	 */
	public function insertParticipant($personId, $educationEventId, $educationEventCourseId)
	{
		return $this->getSkautis()
			->event
			->participantEducationInsert(([
				'ID_EventEducation'       => $educationEventId,
				'ID_Person'               => $personId,
				'ID_EventEducationCourse' => $educationEventCourseId,
			]));
	}

}
