<?php
class Security {
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validatePhone($phone) {
        return preg_match('/^\+?[1-9]\d{1,14}$/', $phone);
    }

    public static function validateCoordinates($lat, $lng) {
        return is_numeric($lat) && is_numeric($lng) &&
               $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180;
    }

    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function sanitizeFileName($filename) {
        return preg_replace('/[^a-zA-Z0-9_.-]/', '', $filename);
    }
}