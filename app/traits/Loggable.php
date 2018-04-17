<?php

namespace App\Traits;

use Tracy\Debugger;

trait Loggable
{
	/**
	 * Logs debug message.
	 *
	 * @param string $message   Formatted message
	 * @param array  $arguments Message arguments
	 */
	protected function logDebug(string $message = '', array $arguments = [])
	{
		$this->log($message, $arguments, Debugger::DEBUG);
	}

	/**
	 * Logs info message.
	 *
	 * @param string $message   Formatted message
	 * @param array  $arguments Message arguments
	 */
	protected function logInfo(string $message = '', array $arguments = [])
	{
		$this->log($message, $arguments, Debugger::INFO);
	}

	/**
	 * Logs warning message.
	 *
	 * @param string $message   Formatted message
	 * @param array  $arguments Message arguments
	 */
	protected function logWarning(string $message = '', array $arguments = [])
	{
		$this->log($message, $arguments, Debugger::WARNING);
	}

	/**
	 * Logs error message.
	 *
	 * @param string $message   Formatted message
	 * @param array  $arguments Message arguments
	 */
	protected function logError(string $message = '', array $arguments = [])
	{
		$this->log($message, $arguments, Debugger::ERROR);
	}

	/**
	 * Logs exception message.
	 *
	 * @param string $message   Formatted message
	 * @param array  $arguments Message arguments
	 */
	protected function logException(string $message = '', array $arguments = [])
	{
		$this->log($message, $arguments, Debugger::EXCEPTION);
	}

	/**
	 * Logs critical message.
	 *
	 * @param string $message   Formatted message
	 * @param array  $arguments Message arguments
	 */
	protected function logCritical(string $message = '', array $arguments = [])
	{
		$this->log($message, $arguments, Debugger::CRITICAL);
	}

	/**
	 * Logs debug message.
	 *
	 * @param string $message   Formatted message
	 * @param array  $arguments Message arguments
	 */
	protected function log(string $message = '', array $arguments = [], string $level = Debugger::ERROR)
	{
		Debugger::log(vsprintf($message, $arguments), $level);
	}
}
