<?php

class ConfigboxLocation {

	public $countryCode;
	public $stateFips;
	public $city;
	public $zipcode;
	public $coords;
	public $metrocode;
	public $areacode;

	public function __construct($record = NULL)
	{
		if(!empty($record)){
			$this->countryCode = $record->country->isoCode;
			$this->stateFips = $record->mostSpecificSubdivision->isoCode;
			$this->city = $record->city->name;
			$this->zipcode = $record->postal->code;
			$this->coords = new stdClass();
			$this->coords->lat = $record->location->latitude;
			$this->coords->lon = $record->location->longitude;
			$this->metrocode = $record->location->metroCode;
			$this->areacode = $record->mostSpecificSubdivision->isoCode;
		}
	}

};