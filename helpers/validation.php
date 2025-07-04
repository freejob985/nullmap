<?php
/**
 * Validation Helper Class
 * 
 * This class provides methods for validating and sanitizing input data.
 * It includes common validation rules and error messages in Arabic.
 */

class Validation {
    /**
     * @var array Validation errors
     */
    private $errors = [];

    /**
     * @var array Custom error messages
     */
    private $messages = [
        'required' => 'حقل %s مطلوب',
        'email' => 'حقل %s يجب أن يكون بريد إلكتروني صحيح',
        'min' => 'حقل %s يجب أن يكون على الأقل %d حروف',
        'max' => 'حقل %s يجب أن لا يتجاوز %d حروف',
        'numeric' => 'حقل %s يجب أن يكون رقم',
        'decimal' => 'حقل %s يجب أن يكون رقم عشري',
        'in' => 'قيمة %s غير صحيحة',
        'unique' => '%s موجود مسبقاً',
        'match' => 'حقل %s غير متطابق',
        'url' => 'حقل %s يجب أن يكون رابط صحيح',
        'date' => 'حقل %s يجب أن يكون تاريخ صحيح',
        'alpha' => 'حقل %s يجب أن يحتوي على حروف فقط',
        'alphanumeric' => 'حقل %s يجب أن يحتوي على حروف وأرقام فقط'
    ];

    /**
     * @var array Field labels in Arabic
     */
    private $labels = [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'role' => 'الدور',
        'is_active' => 'الحالة',
        'country_id' => 'الدولة',
        'city' => 'المدينة',
        'type' => 'النوع',
        'total' => 'العدد',
        'latitude' => 'خط العرض',
        'longitude' => 'خط الطول'
    ];

    /**
     * @var Validation The singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     * 
     * @return Validation
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Validate input data against rules
     * 
     * @param array $data Input data
     * @param array $rules Validation rules
     * @return bool True if validation passes
     */
    public function validate($data, $rules) {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $label = $this->labels[$field] ?? $field;
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                if (is_string($rule)) {
                    $rule = ['rule' => $rule];
                }

                $ruleName = $rule['rule'];
                $params = $rule['params'] ?? [];

                switch ($ruleName) {
                    case 'required':
                        if (empty($value) && $value !== '0') {
                            $this->addError($field, sprintf($this->messages['required'], $label));
                        }
                        break;

                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->addError($field, sprintf($this->messages['email'], $label));
                        }
                        break;

                    case 'min':
                        if (!empty($value) && mb_strlen($value) < $params[0]) {
                            $this->addError($field, sprintf($this->messages['min'], $label, $params[0]));
                        }
                        break;

                    case 'max':
                        if (!empty($value) && mb_strlen($value) > $params[0]) {
                            $this->addError($field, sprintf($this->messages['max'], $label, $params[0]));
                        }
                        break;

                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $this->addError($field, sprintf($this->messages['numeric'], $label));
                        }
                        break;

                    case 'decimal':
                        if (!empty($value) && !preg_match('/^-?\d*\.?\d+$/', $value)) {
                            $this->addError($field, sprintf($this->messages['decimal'], $label));
                        }
                        break;

                    case 'in':
                        if (!empty($value) && !in_array($value, $params)) {
                            $this->addError($field, sprintf($this->messages['in'], $label));
                        }
                        break;

                    case 'unique':
                        if (!empty($value)) {
                            $db = Database::getInstance();
                            $table = $params[0];
                            $column = $params[1];
                            $ignore = $params[2] ?? null;

                            $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
                            $queryParams = [$value];

                            if ($ignore) {
                                $sql .= " AND id != ?";
                                $queryParams[] = $ignore;
                            }

                            $result = $db->fetchOne($sql, $queryParams);
                            if ($result['count'] > 0) {
                                $this->addError($field, sprintf($this->messages['unique'], $label));
                            }
                        }
                        break;

                    case 'match':
                        if (!empty($value) && $value !== $data[$params[0]]) {
                            $this->addError($field, sprintf($this->messages['match'], $label));
                        }
                        break;

                    case 'url':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                            $this->addError($field, sprintf($this->messages['url'], $label));
                        }
                        break;

                    case 'date':
                        if (!empty($value)) {
                            $date = date_parse($value);
                            if ($date['error_count'] > 0) {
                                $this->addError($field, sprintf($this->messages['date'], $label));
                            }
                        }
                        break;

                    case 'alpha':
                        if (!empty($value) && !preg_match('/^[\p{L}\s]+$/u', $value)) {
                            $this->addError($field, sprintf($this->messages['alpha'], $label));
                        }
                        break;

                    case 'alphanumeric':
                        if (!empty($value) && !preg_match('/^[\p{L}\d\s]+$/u', $value)) {
                            $this->addError($field, sprintf($this->messages['alphanumeric'], $label));
                        }
                        break;
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Add validation error
     * 
     * @param string $field Field name
     * @param string $message Error message
     */
    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Get validation errors
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get first error message for a field
     * 
     * @param string $field Field name
     * @return string|null
     */
    public function getError($field) {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Sanitize input data
     * 
     * @param array $data Input data
     * @return array Sanitized data
     */
    public function sanitize($data) {
        $clean = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $clean[$key] = $this->sanitize($value);
            } else {
                $clean[$key] = $this->sanitizeValue($value);
            }
        }

        return $clean;
    }

    /**
     * Sanitize a single value
     * 
     * @param mixed $value Input value
     * @return mixed Sanitized value
     */
    private function sanitizeValue($value) {
        if (is_string($value)) {
            // Remove invisible characters
            $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
            
            // Convert special characters to HTML entities
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            
            // Trim whitespace
            $value = trim($value);
        }

        return $value;
    }

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {}

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup() {}
} 