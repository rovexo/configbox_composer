<?php
class ConfigboxCalculationData {

	/**
	 * @var int ID of the calculation
	 */
	var $id = 49;

	/**
	 * @var string Name of the calculation
	 */
	var $name = '';

	/**
	 * @var string matrix|formula\code
	 */
	var $type = 'matrix';

	/**
	 * @var int For matrix type only. Tells how to round input values 1 means full integers, 10 round tens etc.
	 */
	var $round = 1;

	/**
	 * @var int For matrix type only. 0 means axis value must match exactly. 1 means next higher value used, 2 next lower
	 */
	var $lookup_value = 0;

	/**
	 * @var int For matrix type only. Static number used for multiplying the result
	 */
	var $multiplicator = 1;

	/**
	 * @var int|null For matrix type only. Question used for multiplying the result
	 */
	var $multielementid = NULL;

	/**
	 * @var int|null For matrix type only. Calculation used for multiplying the result
	 */
	var $calcmodel_id_multi = NULL;

	/**
	 * @var int|null For code type only. The question ID for placeholders
	 */
	var $element_id_a = NULL;

	/**
	 * @var int|null For code type only. The question ID for placeholders
	 */
	var $element_id_b = NULL;

	/**
	 * @var int|null For code type only. The question ID for placeholders
	 */
	var $element_id_c = NULL;

	/**
	 * @var int|null For code type only. The question ID for placeholders
	 */
	var $element_id_d = NULL;

	/**
	 * @var string|null For code type only. The code as entered
	 */
	var $code = NULL;

	/**
	 * @var string|null For formula type only. Calculation JSON
	 */
	var $calc = NULL;

	/**
	 * @var int
	 */
	var $product_id = 29;

	/**
	 * @var string question|calculation\none
	 */
	var $row_type = 'question';

	/**
	 * @var int|null
	 */
	var $row_calc_id = NULL;

	/**
	 * @var int|null
	 */
	var $row_element_id = 67;

	/**
	 * @var string question|calculation\none
	 */
	var $column_type = 'calculation';

	/**
	 * @var int|null
	 */
	var $column_calc_id = 42;

	/**
	 * @var int|null
	 */
	var $column_element_id = NULL;
}