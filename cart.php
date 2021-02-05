<?php
class Cart {
	private $sessionId = '', $cookie = false, $itemLimit = 0, $quantityLimit = 99, $items = array(), $attributes = array(), $errors = array();

	/**
	 * Initialize shopping cart
	 *
	 * @param string $sessionId An unique ID for shopping cart session
	 * @param boolean $cookie Store cart items in cookie
	 */
	public function __construct($sessionId = '', $cookie = false) {
		if(!session_id())
			session_start();

		$this->sessionId = (!empty($sessionId)) ? $sessionId : str_replace('.', '_', ((isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '')) . '_cart';
		$this->cookie = ($cookie) ? true : false;

		$this->read();
	}
	/**
	 * Get list of items in cart
	 *
	 * @return array An array of items in the cart
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * Add an item to cart
	 *@param string $name Name of targeted item
	 * @param integer $qty Quantity of item
	 *
	 * @return boolean Result as true/false
	 */
	public function add($name, $qty = 1) {
		if(!$this->isInteger($qty)) {
			$this->errors[] = 'Cart::add($qty): $qty must be integer.';
			return false;
		}

		if($this->itemLimit > 0 && count($this->items) >= $this->itemLimit)
			$this->clear();

		$this->items[$name] = (isset($this->items[$name])) ? ($this->items[$name] + $qty) : $qty;
		$this->items[$name] = ($this->items[$name] > $this->quantityLimit) ? $this->quantityLimit : $this->items[$name];

		$this->write();
		return true;
	}

	/**
	 * Remove item from cart
	 *
	 * @param string $name Name of targeted item
	 */
	public function remove($name) {
		unset($this->items[$name]);
		unset($this->attributes[$name]);

		$this->write();
	}

	/**
	 * Clear all items in the cart
	 */
	public function clear() {
		$this->items = array();
		$this->attributes = array();
		$this->write();
	}

	/**
	 * Check if a string is integer
	 *
	 * @param string $int String to validate
	 *
	 * @return boolean Result as true/false
	 */
	private function isInteger($int) {
		return preg_match('/^[0-9]+$/', $int);
	}

	/**
	 * Read items from cart session
	 */
	private function read() {
		$listItem = ($this->cookie && isset($_COOKIE[$this->sessionId])) ? $_COOKIE[$this->sessionId] : (isset($_SESSION[$this->sessionId]) ? $_SESSION[$this->sessionId] : '');
		$listAttribute = (isset($_SESSION[$this->sessionId . '_attributes'])) ? $_SESSION[$this->sessionId . '_attributes'] : (($this->cookie && isset($_COOKIE[$this->sessionId . '_attributes'])) ? $_COOKIE[$this->sessionId . '_attributes'] : '');

		$items = @explode(';', $listItem);
		foreach($items as $item) {
			if(!$item || !strpos($item, ','))
				continue;

			list($name, $qty) = @explode(',', $item);
			$this->items[$name] = $qty;
		}

		$attributes = @explode(';', $listAttribute);
		foreach($attributes as $attribute) {
			if(!strpos($attribute, ','))
				continue;

			list($key, $value) = @explode(',', $attribute);

			$this->attributes[$key] = $value;
		}
	}

	/**
	 * Write changes to cart session
	 */
	private function write() {
		$_SESSION[$this->sessionId] = '';
		foreach($this->items as $name => $qty) {
			if(!$name)
				continue;

			$_SESSION[$this->sessionId] .= $name . ',' . $qty . ';';
		}

		$_SESSION[$this->sessionId . '_attributes'] = '';
		foreach($this->attributes as $name => $attributes) {
			if(!$name)
				continue;

			foreach($attributes as $key => $value)
			$_SESSION[$this->sessionId . '_attributes'] .= $name . ',' . $key . ',' . $value . ';';
		}

		$_SESSION[$this->sessionId] = rtrim($_SESSION[$this->sessionId], ';');
		$_SESSION[$this->sessionId . '_attributes'] = rtrim($_SESSION[$this->sessionId . '_attributes'], ';');

		if($this->cookie) {
			setcookie($this->sessionId, $_SESSION[$this->sessionId], time() + 604800);
			setcookie($this->sessionId . '_attributes', $_SESSION[$this->sessionId . '_attributes'], time() + 604800);
		}
	}
}
?>