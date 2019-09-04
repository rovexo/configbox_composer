<?php
class KenedoModelLight {

	public $errors;

	function setError($error) {
		$this->errors[] = $error;
	}

	function setErrors($errors) {
		if (is_array($errors) && count($errors)) {
			$this->errors = array_merge((array)$this->errors,$errors);
		}
	}

	function getErrors() {
		return $this->errors;
	}

	function getError() {
		if (is_array($this->errors) && count($this->errors)) {
			return end($this->errors);
		}
		else {
			return '';
		}
	}

}