<?php

namespace App\Models;

use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

/**
 * Meal
 *
 * class for handling meals
 *
 * @created 2012-11-11
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class MealModel extends BaseModel
{

	/**
	 * @var array
	 */
	static public $meals = [
		'fry_dinner'    => 'páteční večeře',
		'sat_breakfast' => 'sobotní snídaně',
		'sat_lunch'     => 'sobotní oběd',
		'sat_dinner'    => 'sobotní večeře',
		'sun_breakfast' => 'nedělní snídaně',
		'sun_lunch'     => 'nedělní oběd',
	];

	/**
	 * @deprecated
	 * @var array
	 */
	static public $dayMeal = [
		"páteční večeře"	=>	"fry_dinner",
		"sobotní snídaně"	=>	"sat_breakfast",
		"sobotní oběd"		=>	"sat_lunch",
		"sobotní večeře"	=>	"sat_dinner",
		"nedělní snídaně"	=>	"sun_breakfast",
		"nedělní oběd"		=>	"sun_lunch"
	];

	/**
	 * @var string
	 */
	protected $table = 'kk_meals';

	/**
	 * @var array
	 */
	protected $columns = [
		"visitor",
		"fry_dinner",
		"sat_breakfast",
		"sat_lunch",
		"sat_dinner",
		"sun_breakfast",
		"sun_lunch"
	];

	/**
	 * @param Context $database
	 */
	public function __construct(Context $database)
	{
		$this->setDatabase($database);
	}

	/**
	 * @return array
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}

	/**
	 * @param  integer $visitorId
	 * @param  array   $data
	 * @return ActiveRow
	 */
	public function updateByVisitor($visitorId, array $data): ActiveRow
	{
		return $this->getDatabase()
			->table($this->getTable())
			->where('visitor', $visitorId)
			->update($data);
	}

    /**
     * @param  int   $visitorId
     * @param  array $values
     * @return ActiveRow|bool
     */
	public function updateOrCreate(int $visitorId, array $values)
    {
        $result = $this->updateByVisitor($visitorId, $values);

        if(!$result) {
            $values['visitor'] = $visitorId;
            $result = $this->create($values);
        }

        return $result;
    }

	/**
	 * Get meals data by visitor id
	 *
	 * @param	integer	visitor id
	 * @return	array
	 */
	public function findByVisitorId($visitorId): array
	{
		$meals = $this->getDatabase()
			->table($this->getTable())
			->where('visitor', $visitorId)
			->limit(1)
			->fetch();

		if(!$meals) {
		    $meals = [];
        } else {
		    $meals = $meals->toArray();
        }

        return $meals;

	}

}
