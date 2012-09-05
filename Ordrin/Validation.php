<?php

class Validation {
	public $errors = array();
	public function validateRequiredField ($value,$name){
		if(empty($value)){
			$this->errors[] = 'Validation - Required (' . $name . ')';
			return false;
		}
		return true;
	}
	public function validateInteger ($value,$name){
		if(!preg_match('/^\d+$/', $value) || $value == ''){
			$this->errors[] = 'Validation - '.$name.' (invalid, must be integer) (' . $value . ')';
			return false;
		}
		return true;	
	}
	public function validateRestaurantID ($value){
		if(!preg_match('/^\d+$/', $value) || $value == ''){
			$this->errors[] = 'Validation - Restaurant ID (invalid, must be integer) (' . $value . ')';
			return false;
		}
		return true;	
	}
	public function validateItemID ($value){
		if(!preg_match('/^\d+$/', $value) || $value == ''){
			$this->errors[] = 'Validation - Item ID (invalid, must be integer) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateQuantity ($value){
		if(!preg_match('/^\d+$/', $value) || $value == ''){
			$this->errors[] = 'Validation - Quantity (invalid, must be integer) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateMoney ($value){
		if(!preg_match('/^\$?\d*(\.\d{2})?$/', $value) || $value == ''){
			$this->errors[] = 'Validation - Money (invalid) (' . $value . ')';
			return false;
		}
		return true;	
	}
	public function validateTrayItems ($value){
		if(!preg_match('/^\d+(,\d+)*(\+\d+(,\d+)*)*/$', $value) || $value == ''){
			$this->errors[] = 'Tray - Validation - Items (invalid, items must be a non-empty array of TrayItems or string tray representation) (' . $value . ')';
			return false;
		}
		return true;	
	}
	public function validateEmail ($value){
		if(!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $value)){
			$this->errors[] = 'Validation - Email (invalid) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateZipCode ($value){
		if(!preg_match('/(^\d{5}$)|(^\d{5}\-\d{4}$)/', $value)){
			$this->errors[] = 'Validation - Zip Code (invalid) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validatePhone ($value){
		if(!preg_match('/^\(?\d{3}\)?[\- .]?\d{3}[\- .]?\d{4}$/', $value)){
			$this->errors[] = 'Validation - Phone Number (invalid, must be in format ###-###-####) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateCity ($value){
		if(!preg_match('/^[A-z.\- ]+$/', $value)){
			$this->errors[] = 'Validation - City (invalid, only letters/spaces allowed) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateState ($value){
		if(!preg_match('/^[a-z]{2}$/i', $value)){
			$this->errors[] = 'Validation - State (invalid, must be two-letter state abbreviation) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateURL ($value){
		if(!preg_match('/^(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?$/', $value)){
			$this->errors[] = 'Validation - URL (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateCVC ($value){
		if(!preg_match('/^\d{3,4}$/', $value)){
			$this->errors[] = 'Validation - CVC (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateExpirationDate ($value){
		if(!preg_match('/^\d{2}\/\d{4}$/', $value)){
			$this->errors[] = 'Validation - Expiration Date (invalid, must be in format mm/yyyy) (' . $value . ')';
			return false;
		}
		return true;
	}
	public function validateCardNumber($number) {
		//Perform a Luhn Test
		$number1 = substr($numebr, 0, strlen($number) - 1);
		$sum = 0;
		for($i = 0; $i < strlen($number1); $i++) {
			$sum += intval(substr($number1, $i, $i + 1));
		}
	
		$delta = array(0,1,2,3,4,-4,-3,-2,-1,0);
		for($i = strlen($number1) - 1; $i >= 0; $i-=2) {
			$sum += $delta[intval(substr($number1, $i, $i + 1))];
		}
	
		$mod10 = 10 - sum % 10;
		if($mod10 == 10) {
			$mod10 = 0;
		}
	
		if ($mod10 == intval(substr($number, strlen($number) - 1, strlen($number)))) {
			return true;
		}
		$this->errors[] = 'Validation - Card Number (' . $number . ')';
		return false;
	}
}