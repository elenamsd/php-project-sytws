<?php
function isValidPassword($password) {
	return strlen($password) >= 8
		&& preg_match('/[A-Z]/', $password)
		&& preg_match('/[0-9]/', $password)
		&& preg_match('/[^\w\s]/', $password);
}
