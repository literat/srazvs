<?php

namespace App;

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

	/** @var array	meals */
	public $day_meal = array();

	/** Constructor */
	public function __construct($database)
	{
		$this->dbColumns = array(
			"visitor",
			"fry_dinner",
			"sat_breakfast",
			"sat_lunch",
			"sat_dinner",
			"sun_breakfast",
			"sun_lunch"
		);

		$this->dayMeal = array(
			"páteční večeře"	=>	"fry_dinner",
			"sobotní snídaně"	=>	"sat_breakfast",
			"sobotní oběd"		=>	"sat_lunch",
			"sobotní večeře"	=>	"sat_dinner",
			"nedělní snídaně"	=>	"sun_breakfast",
			"nedělní oběd"		=>	"sun_lunch"
		);
		$this->dbTable = "kk_meals";
		$this->database = $database;
	}

	/**
	 * Modify record
	 *
	 * @param	int		$id			Id of record
	 * @param	array	$db_data	Array of data
	 * @return	bool
	 */
	public function modify($id, array $dbData)
	{
		$result = $this->database
			->table($this->dbTable)
			->where('visitor', $id)
			->update($dbData);

		return $result;
	}

	function getMeals($visitorId)
	{
		$meals = "<tr>";
		$meals .= " <td class='progPart'>";

		$result = $this->database
			->table($this->dbTable)
			->where('vsitor', $visitorId)
			->fetchAll();

		if(!$result){
			$meals .= "<div class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</div>\n";
		} else {
			foreach($result as $mealData){
				$meals .= "<div class='block'>".$mealData['fry_dinner'].", ".$mealData['sat_breakfast']." - ".$mealData['sat_lunch']." : ".$mealData['sat_dinner']."</div>\n";

			}
		}

		$meals .= "</td>";
		$meals .= "</tr>";

		return $meals;
	}

	/**
	 * Render HTML Meals <select>
	 *
	 * @param	string	value of selected meal
	 * @param	string	if select is disabled
	 * @return	string	html <select>
	 */
	public function renderHtmlMealsSelect($mealsValue, $disabled)
	{
		// order must be firtsly NO and then YES
		// first value is displayed in form as default
		$mealArray = array("ne" => "ne","ano" => "ano");
		$yesNoArray = array("ne", "ano");

		$htmlSelect = "";
		foreach($this->dayMeal as $title => $varName){
			if(preg_match("/breakfast/", $varName))	$mealIcon = "breakfast";
			if(preg_match("/lunch/", $varName))		$mealIcon = "lunch";
			if(preg_match("/dinner/", $varName))	$mealIcon = "dinner";

			$htmlSelect .= "<span style='display:block;font-size:11px;'>".$title.":</span>\n";
			$htmlSelect .= "<img style='width:18px;' src='".IMG_DIR."icons/".$mealIcon.".png' />\n";
			$htmlSelect .= "<select ".$disabled." style='width:195px; font-size:11px;margin-left:5px;' name='".$varName."'>\n";

			foreach ($yesNoArray as $key){
				if($key == $mealsValue[$varName]){
					$selected = "selected";
				}
				else $selected = "";
				$htmlSelect .= "<option value='".$key."' ".$selected.">".$key."</option>";
			}
			$htmlSelect .= "</select><br />\n";
		}

		return $htmlSelect;
	}

	/**
	 * Get meals data into array
	 *
	 * @param	integer	visitor id
	 * @return	array	meal => ano|ne
	 */
	public function getMealsArray($visitorId)
	{
		$data = $this->database
			->table($this->dbTable)
			->where('visitor', $visitorId)
			->limit(1)
			->fetch();

		foreach($this->dbColumns as $column) {
			$$column = requested($column, $data[$column]);
			$meals[$column] = $$column;
		}

		return $meals;
	}
}
