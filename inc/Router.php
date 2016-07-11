<?php

/**
 * Router
 *
 * This controller routes all incoming requests to the appropriate controller
 *
 * @author      Tomas Litera    <tomaslitera@hotmail.com>
 * @version     2014-02-19
 * @package     Nix
 * @subpackage  Routers
 */
class Router
{
	/** @var Http */
	public $Http;

	/** @var bool */
	public $routed = false;

	/** @var array */
	protected $defaults = array();

	/** @var string */
	protected $request;

	/** @var array */
	protected $routing = array(
		'controller' => '',
		'module' => array(),
		'action' => '',
		'service' => '',
	);

	/** @var array */
	protected $args = array();

	/** @var array */
	protected $params = array();

	/** @var null|array */
	protected $__tempArgs;

	/**
	 * Constructor
	 *
	 * @return Router
	 */
	public function __construct()
	{
		$this->Http = new Http();
		//$this->request = $this->Http->request->request;
		$this->request = Http::$request->request;
		$this->params = $_GET;
	}

	/**
	 * Sets defaults routing setting
	 *
	 * @param array $defaults defaults routing
	 * @return Router
	 */
	public function setDefaults($defaults = null)
	{
		$this->defaults = (array) $defaults;
		return $this;
	}

	/**
	 * Proccesses service request
	 *
	 * @param string $name service name
	 * @return bool
	 */
	public function setService($name)
	{
		$name = strtolower($name);
		if(substr($this->request, -strlen($name)) != $name) {
			return false;
		}

		$this->request = substr($this->request, 0, -strlen($name));
		$this->routing['service'] = $name;
		return true;
	}

	/**
	 * Connects to url
	 *
	 * @param string $route routing expression
	 * @param array $defaults default routing settings
	 * @param bool $allowArg allow undefined args?
	 * @param bool $allowParams allow query params?
	 * @return bool
	 */
	public function connect($route, $defaults = array(), $allowArgs = false, $allowParams = false)
	{
		if($this->routed) {
			return false;
		}

		if(!$allowParams && !empty($this->params)) {
			return false;
		}

		$routing = array();
		$route = trim($route, '/');
		$parts = preg_split('#\<\:\w+( [^>]+)?\>#', $route);

		if(count($parts) > 1) {
			preg_match_all('#\<\:(\w+)( [^>]+)?\>#', $route, $matches);
			$route = '';
			foreach($matches[2] as $i => $match) {
				if(empty($match)) {
					$match = $allowArgs ? '[^/]+' : '[^/]+?';
				}
				# escape other text
				$route .= preg_quote($parts[$i], '#') . '(' . trim($match) . ')';
			}

			if(!empty($parts[$i + 1])) {
				$route .= $parts[$i + 1];
			}
		}

		if(!isset($matches[1])) {
			$matches[1] = array();
		}

		if($allowArgs) {
			$route .= '(?:/(.*?))?';
			$matches[1][] = '__args';
		}

		if(!preg_match("#^$route$#", $this->request, $m)) {
			return false;
		}

		if(count($m) == 0) {
			return false;
		}

		array_shift($m);
		foreach ($matches[1] as $i => $key) {
			if(!isset($m[$i])) {
				break;
			}

			if($key == 'module') {
				$routing['module'][] = $m[$i];
			} else {
				$routing[$key] = $m[$i];
			}
		}

		if($allowArgs && !empty($routing['__args'])) {
			$args = explode('/', $routing['__args']);
			foreach($args as $i => $arg) {
				$routing[$i + 1] = $arg;
			}

			unset($routing['__args']);
		}

		$routing = array_merge(
		array(
			'controller' => '',
			'action' => 'index',
			'module' => array()),
			$this->defaults,
			(array) $defaults,
			$routing
		);

		static $__routing = array('controller' => 1, 'module' => 1,     'action' => 1, 'service' => 1);
		foreach($routing as $key => $val) {
			if(isset($__routing[$key])) {
				$this->routing[$key] = $val;
			} else {
				$this->args[$key] = $val;
			}
		}

		return $this->routed = true;
	}

	/**
	 * Processes the application ur
	 *
	 * @param string $url url
	 * @param array $args rewrite args
	 * @param array|false $params rewrite params
	 * @return string
	 */
	public function url($url, $args = array(), $params = false)
	{
		if(empty($url)) {
			$url = $this->request;
		} else {
			$this->__tempArgs = (array) $args;
			$url = preg_replace_callback('#\<\:(controller|action|module|service)(\[\d+\])?\>#i', array($this, 'cbUrlRouting'), $url);
			$url = str_replace('<:url:>', $this->request, $url);
			$url = preg_replace_callback('#\<\:([a-z0-9]+)\>#i', array($this, 'cbUrlArgs'), $url);
			$this->__tempArgs = null;
		}

		if($params !== false) {
			$p = array();
			$params = array_merge($this->params, (array) $params);
			foreach($params as $name => $value) {
				if($value == null) {
					continue;
				}
				$p[] = urlencode($name) . '=' . urlencode($value);
			}


			if(!empty($p)) {
				$url .= '?' . implode('&', $p);
			}
		}

		return $url;
	}

	/**
	 * Returns arg
	 *
	 * @param string $key arg name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getArg($key, $default = null)
	{
		if(!isset($this->args[$key])) {
			return $default;
		}

		return $this->args[$key];
	}

	/**
	 * Returns param
	 *
	 * @param string $key param name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParam($key, $default = null)
	{
		if(!isset($this->params[$key])) {
			return $default;
		}

		return $this->params[$key];
	}

	/**
	 * Returns args array
	 *
	 * @return array
	 */
	public function getArgs()
	{
		return $this->args;
	}

	/**
	 * Returns params
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Returns sanitized routing
	 *
	 * @param bool $normalized return without name camelizing
	 * @return array
	 */
	public function getRouting($normalized = true)
	{
		if(!$normalized) {
			return $this->routing;
		}

		return array(
			'controller' => Tools::camelize($this->routing['controller']),
			'module' => array_map(array('Tools', 'camelize'), $this->routing['module']),
			'action' => $this->routing['action'],
			//'action' => Tools::camelize($this->routing['action']),
			'service' => Tools::camelize($this->routing['service']),
		);
	}

	/**
	 * Getter
	 *
	 * @param string $key arg name
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->getArg($key);
	}

	/**
	 * Setter
	 *
	 * @throws Exception
	 */
	public function __set($key, $value)
	{
		throw new Exception("You can't set 'Router::\$$key' variable.");
	}

	/**
	 * Issetter
	 *
	 * @return bool
	 */
	public function __isset($key)
	{
		return isset($this->args[$key]);
	}

	/**
	 * Unsetter
	 *
	 * @throws Exception
	 */
	public function __unset($key)
	{
		throw new Exception("You can't unset 'Router::\$$key' variable.");
	}

	/**
	 * Url args callback
	 *
	 * @param array $matches
	 * @return string
	 */
	protected function cbUrlArgs($matches)
	{
		$args = array_merge($this->args, $this->__tempArgs);
		if(isset($args[$matches[1]])) {
			return $args[$matches[1]];
		} else {
			return $matches[1];
		}
	}

	/**
	 * Url routing callback
	 *
	 * @param array $matches
	 * @return string
	 */
	protected function cbUrlRouting($matches)
	{
		$routing = array_merge($this->routing, $this->__tempArgs);
		if(isset($matches[2]) && isset($routing['module'][$matches[2]])) {
			return $routing['module'][$matches[2]];
		} elseif(isset($routing[$matches[1]])) {
			return $routing[$matches[1]];
		} else {
			return $matches[1];
		}
	}
}
