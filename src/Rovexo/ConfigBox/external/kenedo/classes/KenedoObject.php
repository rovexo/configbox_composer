<?php
/**
 * Class KenedoObject
 * Extending stdClass?? Many code inspectors mark dynamically created object vars, unless it's a stdClass.
 * And with KenedoObject's __set_state you can serialize objects, which is used for our caching.
 *
 */
class KenedoObject extends stdClass {
	
	public static function __set_state($array) {
		return new KenedoObject($array);
	}

	/**
	 * @param object|array|NULL $data Data you want to set as object vars
	 */
	function __construct($data = NULL) {
		if (is_object($data) || is_array($data)) {
			foreach ($data as $key=>$value) {
				$this->$key = $value;
			}
		}
	}
}