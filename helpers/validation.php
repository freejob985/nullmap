<?php
/**
 * Validation Helper Functions
 */

class Validation {
    private static $instance = null;
    private $errors = [];

    private function __construct() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function validate($data, $rules) {
        $this->errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            if (!isset($data[$field]) && !in_array('optional', $fieldRules)) {
                $this->errors[$field][] = "The {$field} field is required.";
                continue;
            }

            if (isset($data[$field])) {
                $value = $data[$field];
                
                foreach ($fieldRules as $rule) {
                    if (is_string($rule)) {
                        $this->applyRule($field, $value, $rule);
                    } elseif (is_array($rule)) {
                        $this->applyRuleWithParams($field, $value, $rule);
                    }
                }
            }
        }

        return empty($this->errors);
    }

    private function applyRule($field, $value, $rule) {
        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field][] = "The {$field} field is required.";
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "The {$field} must be a valid email address.";
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    $this->errors[$field][] = "The {$field} must be a number.";
                }
                break;

            case 'latitude':
                if (!is_numeric($value) || $value < -90 || $value > 90) {
                    $this->errors[$field][] = "The {$field} must be a valid latitude (-90 to 90).";
                }
                break;

            case 'longitude':
                if (!is_numeric($value) || $value < -180 || $value > 180) {
                    $this->errors[$field][] = "The {$field} must be a valid longitude (-180 to 180).";
                }
                break;
        }
    }

    private function applyRuleWithParams($field, $value, $rule) {
        $ruleName = $rule[0];
        $params = array_slice($rule, 1);

        switch ($ruleName) {
            case 'min':
                if (strlen($value) < $params[0]) {
                    $this->errors[$field][] = "The {$field} must be at least {$params[0]} characters.";
                }
                break;

            case 'max':
                if (strlen($value) > $params[0]) {
                    $this->errors[$field][] = "The {$field} must not exceed {$params[0]} characters.";
                }
                break;

            case 'enum':
                if (!in_array($value, $params)) {
                    $validValues = implode(', ', $params);
                    $this->errors[$field][] = "The {$field} must be one of: {$validValues}.";
                }
                break;
        }
    }

    public function getErrors() {
        return $this->errors;
    }

    public function sanitize($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove HTML and PHP tags
                $value = strip_tags($value);
                // Convert special characters to HTML entities
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                // Trim whitespace
                $value = trim($value);
            } elseif (is_array($value)) {
                $value = $this->sanitize($value);
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }

    public static function getRules() {
        return [
            'user' => [
                'name' => ['required', ['min', 2], ['max', 255]],
                'email' => ['required', 'email', ['max', 255]],
                'password' => ['required', ['min', 6], ['max', 255]],
                'role' => ['required', ['enum', 'admin', 'user']],
                'is_active' => ['required', ['enum', 0, 1]]
            ],
            'country' => [
                'name' => ['required', ['min', 2], ['max', 255]],
                'city' => ['required', ['min', 2], ['max', 255]],
                'latitude' => ['required', 'latitude'],
                'longitude' => ['required', 'longitude']
            ],
            'place' => [
                'name' => ['required', ['min', 2], ['max', 255]],
                'total' => ['required', 'numeric'],
                'type' => ['required', ['enum', 'private', 'government']],
                'country_id' => ['required', 'numeric'],
                'city' => ['required', ['min', 2], ['max', 255]],
                'latitude' => ['required', 'latitude'],
                'longitude' => ['required', 'longitude']
            ]
        ];
    }
} 