<?php

namespace App\Services\Skautis;

class EventService extends SkautisService
{
	/**
	 * @param  void
	 * @return \stdClass
	 *
	 * @throws \Skautis\Wsdl\WsdlException
	 */
	public function insertParticipant($loginId, $personId, $educationEventId, $educationEventCourseId)
	{
		return $this->getSkautis()
			->event
			->participantEducationInsert(([
				'ID_EventEducation'       => $educationEventId,
				'ID_Person'               => $personId,
				'ID_EventEducationCourse' => $educationEventCourseId,
				'ID_Login'                => $loginId,
			]));
	}

	/**
	 * @param  int       $loginId
	 * @param  int       $educationEventCourseId
	 * @param  string    $phone
	 * @return \stdClass
	 *
	 * @throws \Skautis\Wsdl\WsdlException
	 */
	public function insertEnroll($loginId, $educationEventCourseId, $phone)
	{
		return $this->getSkautis()
			->event
			->participantEducationInsertEnroll(([
				'ID_EventEducationCourse' => $educationEventCourseId,
				'ID_Login'                => $loginId,
				'Phone'                   => $phone,
				'Acknownledgement'        => true,
				'Affirmation'             => true,
			]));
	}
}
