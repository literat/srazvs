<?php
/**
 * Meal
 *
 * class for handling meals
 *
 * @created 2012-11-11
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class Meal extends Component
{
	/** @var meeting ID */
	private $meeting;
	
	/** @var array	meals */
	public $day_meal = array();
	
	/** Constructor */
	public function __construct($meeting = NULL)
	{
		$this->meeting = $meeting;
		$this->DB_columns = array(
			"visitor",
			"fry_dinner",
			"sat_breakfast",
			"sat_lunch",
			"sat_dinner",
			"sun_breakfast",
			"sun_lunch"
		);
							
		$this->day_meal = array(
			"páteční večeře"	=>	"fry_dinner",
			"sobotní snídaně"	=>	"sat_breakfast",
			"sobotní oběd"		=>	"sat_lunch",
			"sobotní večeře"	=>	"sat_dinner",
			"nedělní snídaně"	=>	"sun_breakfast",
			"nedělní oběd"		=>	"sun_lunch"
		);
		$this->dbTable = "kk_meals";
	}
	
	public function getMeals()
	{
		return (array)$this->meals;
	}
	
	/**
	 * Render HTML Meals <select>
	 *
	 * @param	string	value of selected meal
	 * @param	string	if select is disabled
	 * @return	string	html <select>
	 */
	public function renderHtmlMealsSelect($meals_value, $disabled)
	{
		// order must be firtsly NO and then YES
		// first value is displayed in form as default
		$meal_array = array("ne" => "ne","ano" => "ano");
		$yes_no = array("ne", "ano");
		
		$html_select = "";
		foreach($this->day_meal as $title => $var_name){
			if(preg_match("/breakfast/", $var_name))	$mealIcon = "breakfast";
			if(preg_match("/lunch/", $var_name))		$mealIcon = "lunch";
			if(preg_match("/dinner/", $var_name))		$mealIcon = "dinner";
			
			$html_select .= "<span style='display:block;font-size:11px;'>".$title.":</span>\n";
			$html_select .= "<img style='width:18px;' src='".$GLOBALS['ICODIR']."small/".$mealIcon.".png' />\n";
			$html_select .= "<select ".$disabled." style='width:195px; font-size:11px;margin-left:5px;' name='".$var_name."'>\n";
			
			foreach ($yes_no as $key){
				if($key == $meals_value[$var_name]){
					$selected = "selected";
				}
				else $selected = "";
				$html_select .= "<option value='".$key."' ".$selected.">".$key."</option>";
			}
			$html_select .= "</select><br />\n";
		}
				
		return $html_select;
	}
}