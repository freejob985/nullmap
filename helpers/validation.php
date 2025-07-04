<?php
/**
 * Validation Helper Functions
 */

class Validation {
    private static $instance = null;
    private $errors = [];

    private $messages = [
        'required' => 'حقل :field مطلوب',
        'email' => 'يجب أن يكون :field بريداً إلكترونياً صحيحاً',
        'min' => 'يجب أن يكون :field على الأقل :param حروف',
        'max' => 'يجب أن لا يتجاوز :field :param حروف',
        'numeric' => 'يجب أن يكون :field رقماً',
        'in' => 'قيمة :field غير صالحة'
    ];

    private $fields = [
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'name' => 'الاسم',
        'role' => 'الدور',
        'is_active' => 'حالة الحساب'
    ];

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
            foreach ($fieldRules as $rule) {
                if (is_string($rule)) {
                    $ruleName = $rule;
                    $param = null;
                } else {
                    list($ruleName, $param) = explode(':', $rule);
                }
                
                $value = $data[$field] ?? null;
                $fieldName = $this->fields[$field] ?? $field;
                
                switch ($ruleName) {
                    case 'required':
                        if (empty($value)) {
                            $this->addError($field, str_replace(':field', $fieldName, $this->messages['required']));
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->addError($field, str_replace(':field', $fieldName, $this->messages['email']));
                        }
                        break;
                        
                    case 'min':
                        if (!empty($value) && strlen($value) < $param) {
                            $message = str_replace([':field', ':param'], [$fieldName, $param], $this->messages['min']);
                            $this->addError($field, $message);
                        }
                        break;
                        
                    case 'max':
                        if (!empty($value) && strlen($value) > $param) {
                            $message = str_replace([':field', ':param'], [$fieldName, $param], $this->messages['max']);
                            $this->addError($field, $message);
                        }
                        break;
                        
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $this->addError($field, str_replace(':field', $fieldName, $this->messages['numeric']));
                        }
                        break;
                        
                    case 'in':
                        $allowedValues = explode(',', $param);
                        if (!empty($value) && !in_array($value, $allowedValues)) {
                            $this->addError($field, str_replace(':field', $fieldName, $this->messages['in']));
                        }
                        break;
                }
            }
        }
        
        return empty($this->errors);
    }

    public function sanitize($data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = strip_tags(trim($value));
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    public function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function hasErrors() {
        return !empty($this->errors);
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