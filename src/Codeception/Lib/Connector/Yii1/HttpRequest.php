<?php

namespace Codeception\Lib\Connector\Yii1;

use Yii;

class HttpRequest extends \CHttpRequest
{
	private $_headers = [];

	protected $_cookies;

	public function setHeader($name, $value)
	{
		$this->_headers[$name] = $value;
	}

	public function getHeader($name, $default = '')
	{
		return $this->_headers[$name] ?? $default;
	}

	public function getAllHeaders()
	{
		return $this->_headers;
	}

	public function getCookies()
	{
		if ($this->_cookies === null) {
			$this->_cookies = new CodeceptionCookieCollection($this);
		}
		return $this->_cookies;
	}

	public function redirect($url, $terminate = true, $statusCode = 302)
	{
		$this->setHeader('Location', $url);
		if ($terminate) {
			Yii::app()->end(0, false);
		}
	}
}

class CodeceptionCookieCollection extends \CCookieCollection
{
	protected function addCookie($cookie)
	{
		$_COOKIE[$cookie->name] = $cookie->value;
	}

	protected function removeCookie($cookie)
	{
		unset($_COOKIE[$cookie->name]);
	}
}
