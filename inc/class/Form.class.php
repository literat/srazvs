<?php

//namespace Forms;

/**
 * Form
 *
 * Creates and renders HTML forms
 *
 * @created 2012-09-23
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */ 
class Form
{
	/** konstruktor */
	public function __construct()
	{		
	}
	
	/**
	 * Render check box
	 *
	 * @param	string	name
	 * @param	mixed	value
	 * @param	var		variable that match with value
	 * @return	string	html of chceck box
	 */
	public static function renderHtmlCheckBox($name, $value, $checked_variable)
	{
		if($checked_variable == $value) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		$html_checkbox = "<input name='".$name."' type='checkbox' value='".$value."' ".$checked." />";
	
		return $html_checkbox;
	}

	/**
	 * Render select box
	 *
	 * @param	string	name
	 * @param	array	content of slect box
	 * @param	var		variable that match selected option
	 * @param	string	inline styling
	 * @return	string	html of select box
	 */
	public static function renderHtmlSelectBox($name, $select_content, $selected_option, $inline_style = NULL)
	{
		if(isset($inline_style) && $inline_style != NULL){
			$style = " style='".$inline_style."'";
		} else {
			$style = "";
		}
		$html_select = "<select name='".$name."'".$style.">";
		foreach ($select_content as $key => $value) {
			if($key == $selected_option) {
				$selected = 'selected';
			} else {
				$selected = '';
			}
			$html_select .= "<option value='".$key."' ".$selected.">".$value."</option>";
		}
		$html_select .= '</select>';
	
		return $html_select;
	}
}