<?php declare(strict_types=1);

class Validator {
    // String Validation
    protected static function requiredCheck($data): bool {
        if(!isset($data) || is_null($data)) return false;
        else if(is_string($data) && strlen(trim($data)) < 1) return false;
        return true;
    }

    protected static function emailCheck($data): bool {
        if(filter_var($data, FILTER_VALIDATE_EMAIL)) return true;
        return false;
    }

    protected static function minmaxCheck($data, array $matches): bool {
        $minmax = $matches[1];
        $length = intval($matches[2]);

        $is_numeric = is_int($data) || is_float($data);
        $is_string = is_string($data);
        $evaluatee = $is_numeric ? $data : ($is_string ? strlen($data) : null);

        if(!($is_numeric || $is_string) || $evaluatee == null) return false;
        else if($minmax == "min" && $evaluatee < $length) return false;
        else if($minmax == "max" && $evaluatee > $length) return false;
        return true;
    }

    protected static function enumCheck($data, array $matches): bool {
        $list = explode(",", $matches[1]);

        if(!is_string($data) || !in_array($data, $list)) return false;
        return true;
    }

    protected static function patternCheck($data, array $matches): bool {
        $pattern = "/$matches[1]/";

        if(!is_string($data) || !preg_match($pattern, $data)) return false;
        return true;
    }

    // File Validation
    protected static function minmaxFileCountCheck($data, array $matches): bool {
        $minmax = $matches[1];
        $length = intval($matches[2]);
        if(!is_array($data["name"]) && $length == 1) return true; // ignore min/max, length 1 is absolutely in criteria
        else if(is_array($data["name"]) && $minmax == "max") return count($data["name"]) <= $length;
        else if(is_array($data["name"]) && $minmax == "min") return count($data["name"]) >= $length;
        return false;
    }

    protected static function minmaxSizeCheck($data, array $matches): bool {
        $minmax = $matches[1];
        $size = intval($matches[2]);

        if(!is_array($data["size"]) && $minmax == "min") return $data["size"] >= $size;
        else if(!is_array($data["size"]) && $minmax == "max") return $data["size"] <= $size;
        else if(is_array($data["size"]) && $minmax == "min") return min($data["size"]) >= $size;
        else if(is_array($data["size"]) && $minmax == "max") return max($data["size"]) <= $size;
        return false;
    }

    protected static function isPdf($data): bool {
        if(!is_array($data["type"]) && $data["type"] != "application/pdf") return false;
        else if(is_array($data["type"])) {
            foreach($data["type"] as $type) {
                if($type != "application/pdf") return false;
            }
        }
        return true;
    }

    protected static function isImage($data): bool {
        if(!is_array($data["type"]) && substr($data["type"], 0, 6) != "image/") return false;
        else if(is_array($data["type"])) {
            foreach($data["type"] as $type) {
                if(substr($type, 0, 6) != "image/") return false;
            }
        }
        return true;
    }

    protected static function mimeCheck($data, array $matches): bool {
        $list = explode(",", $matches[1]);

        if(!is_array($data["type"]) && !in_array($data['type'], $list)) return false;
        else if(is_array($data["type"])) {
            foreach($data["type"] as $type) {
                if(!in_array($type, $list)) return false;
            }
        }
        return true;
    }

    // Validation method
    public static function validate($data, array $rules): ?string {
        $has_required = in_array("required", array_keys($rules));

        foreach($rules as $rule => $message) {
            // Type Validation
            if($rule == "required" && !self::requiredCheck($data)) return $message;
            else if(!$has_required && (!isset($data) || is_null($data))) return null; // ignore if empty and not required
            else if($rule == "string" && !is_string($data)) return $message;
            else if($rule == "int" && !is_int($data)) return $message;
            else if($rule == "float" && !is_float($data)) return $message;
            // String Validation
            else if($rule == "email" && !self::emailCheck($data)) return $message;
            else if(preg_match("/^(min|max):(\d+)$/", $rule, $matches) && !self::minmaxCheck($data, $matches)) return $message;
            else if(preg_match("/^enum:(.+)$/", $rule, $matches) && !self::enumCheck($data, $matches)) return $message;
            else if(preg_match("/^pattern:(.+)$/", $rule, $matches) && !self::patternCheck($data, $matches)) return $message;
            // File Validation
            else if($rule == "pdf" && !self::isPdf($data)) return $message;
            else if($rule == "image" && !self::isImage($data)) return $message;
            else if(preg_match("/^mime:(.+)$/", $rule, $matches) && !self::mimeCheck($data, $matches)) return $message;
            else if(preg_match("/^(min|max)_size:(\d+)$/", $rule, $matches) && !self::minmaxSizeCheck($data, $matches)) return $message;
            else if(preg_match("/^(min|max)_files:(\d+)$/", $rule, $matches) && !self::minmaxFileCountCheck($data, $matches)) return $message;
        }

        return null;
    }

    public static function validateAll(array $data, array $fieldAndRules): ?array {
        $errorValidation = [];

        foreach($fieldAndRules as $field => $rules) {
            $validationResult = self::validate($data[$field], $rules);
            if($validationResult !== null) {
                $errorValidation[$field] = $validationResult;
            }
        }

        if(count($errorValidation) < 1) return null;
        return $errorValidation;
    }

    public static function fromHTTPRequest(array $fieldAndRules): ?array {
        $errorValidation = [];

        foreach($fieldAndRules as $field => $rules) {
            $is_file = strlen($field) > 5 && preg_match("/^file:(.+)$/", $field, $file_field_match);
            $validationResult = self::validate(
                $is_file
                    ? ($_FILES[$file_field_match[1]] ?: null)
                    : ($_REQUEST[$field] ?: null)
                , $rules
            );

            if($validationResult !== null) {
                $errorValidation[$is_file ? $file_field_match[1] : $field] = $validationResult;
            }
        }

        if(count($errorValidation) < 1) return null;
        return $errorValidation;
    }
}
