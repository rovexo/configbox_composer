<?php

class ConfigboxJsonResponse {

	protected $success = null;
	protected $feedback = '';
	protected $errors = array();
	protected $validationIssues = array();

	static function makeOne() {
		return new self;
	}

	function setSuccess($success) {
		$this->success = $success;
		return $this;
	}

	function setFeedback($feedback) {
		$this->feedback = $feedback;
		return $this;
	}

	function setErrors($errors) {
		$this->errors = $errors;
		return $this;
	}

	function setValidationIssues($issues) {
		$this->validationIssues = $issues;
		return $this;
	}

	function setCustomData($key, $value) {
		$this->$key = $value;
		return $this;
	}

	function toJson() {
		$data = get_object_vars($this);
		return json_encode($data);
	}

}