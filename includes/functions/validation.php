<?php
/**
 * Validation Helper Functions
 * 
 * This file contains functions for input validation.
 */

/**
 * Validate required fields
 * 
 * @param array $data Input data
 * @param array $fields Required field names
 * @return array Array of error messages, empty if validation passes
 */
function validateRequired($data, $fields) {
    $errors = [];
    
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $errors[$field] = "حقل {$field} مطلوب";
        }
    }
    
    return $errors;
}

/**
 * Validate email format
 * 
 * @param string $email Email address
 * @return bool Whether email is valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * 
 * @param string $password Password to validate
 * @return array Array of error messages, empty if validation passes
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "يجب أن تكون كلمة المرور 8 أحرف على الأقل";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "يجب أن تحتوي كلمة المرور على حرف كبير واحد على الأقل";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "يجب أن تحتوي كلمة المرور على حرف صغير واحد على الأقل";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "يجب أن تحتوي كلمة المرور على رقم واحد على الأقل";
    }
    
    return $errors;
}

/**
 * Validate coordinates
 * 
 * @param float $latitude Latitude
 * @param float $longitude Longitude
 * @return bool Whether coordinates are valid
 */
function validateCoordinates($latitude, $longitude) {
    return is_numeric($latitude) &&
           is_numeric($longitude) &&
           $latitude >= -90 &&
           $latitude <= 90 &&
           $longitude >= -180 &&
           $longitude <= 180;
}

/**
 * Sanitize string input
 * 
 * @param string $input Input string
 * @return string Sanitized string
 */
function sanitizeString($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate place type
 * 
 * @param string $type Place type
 * @return bool Whether type is valid
 */
function validatePlaceType($type) {
    return in_array($type, ['خاص', 'حكومة']);
}

/**
 * Validate user role
 * 
 * @param string $role User role
 * @return bool Whether role is valid
 */
function validateUserRole($role) {
    return in_array($role, ['admin', 'user']);
}

/**
 * Validate integer value
 * 
 * @param mixed $value Value to validate
 * @param int $min Minimum value (optional)
 * @param int $max Maximum value (optional)
 * @return bool Whether value is valid
 */
function validateInteger($value, $min = null, $max = null) {
    if (!is_numeric($value) || intval($value) != $value) {
        return false;
    }
    
    $value = intval($value);
    
    if ($min !== null && $value < $min) {
        return false;
    }
    
    if ($max !== null && $value > $max) {
        return false;
    }
    
    return true;
} 