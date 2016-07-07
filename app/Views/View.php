<?php

namespace App;

/**
 * View
 *
 * class for templating
 *
 * @created 2013-02-18
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class View
{
	/**
     * Holds variables assigned to template
	 *
	 * @var	array	data[]
	 */
    private $data = array();

    /**
     * Holds render status of view
	 *
	 * @var	bool	render
     */
    private $render = FALSE;

	/** @var	string	template */
	private $template = NULL;

    /** Construct */
    public function __construct()
    {
    }

	/**
	 * Accept a template to load
	 *
	 * @param	string	$template
	 */
	public function loadTemplate($template)
	{
		$this->template = $template;
		//compose file name
        $file = TEMPLATE_DIR.strtolower($template).'.tpl';

        if (file_exists($file)){
            /**
             * trigger render to include file when this model is destroyed
             * if we render it now, we wouldn't be able to assign variables
             * to the view!
             */
            $this->render = $file;
        }
	}

    /**
     * Receives assignments from controller and stores in local data array
     *
     * @param	mixed	$variable
     * @param´	mixed	$value
     */
    public function assign($variable , $value)
    {
        $this->data[$variable] = $value;
    }

    /**
     * Render the output directly to the page, or optionally, return the
     * generated output to caller.
     *
     * @param $direct_output Set to any non-TRUE value to have the
     * output returned rather than displayed directly.
     */
    public function render($direct_output = TRUE)
    {
        // Turn output buffering on, capturing all output
        if ($direct_output !== TRUE){
            ob_start();
        }

        // Parse data variables into local variables
        $data = $this->data;

        // Get template
        include_once($this->render);

        // Get the contents of the buffer and return it
        if ($direct_output !== TRUE){
            return ob_get_clean();
        }
    }

	/** Destruct */
    public function __destruct()
    {
    }
}
