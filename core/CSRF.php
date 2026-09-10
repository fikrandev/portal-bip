<?php
/**
 * Portal BIP - CSRF Token Manager
 * 
 * Generates and validates CSRF tokens to prevent cross-site request forgery.
 * Tokens are stored in the session and validated on every POST request.
 */

class CSRF
{
    /**
     * Generate a new CSRF token (or return existing one)
     */
    public static function token(): string
    {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Generate a hidden input field with the CSRF token
     */
    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Generate a meta tag for AJAX requests
     */
    public static function meta(): string
    {
        $token = self::token();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }

    /**
     * Validate the submitted CSRF token
     * 
     * @param bool $regenerate Whether to regenerate token after successful validation (default: true)
     */
    public static function validate(bool $regenerate = true): bool
    {
        $submittedToken = $_POST[CSRF_TOKEN_NAME] 
            ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
            ?? '';

        if (empty($submittedToken)) {
            $rawInput = file_get_contents('php://input');
            if (!empty($rawInput)) {
                $json = json_decode($rawInput, true);
                if (is_array($json) && !empty($json[CSRF_TOKEN_NAME])) {
                    $submittedToken = $json[CSRF_TOKEN_NAME];
                }
            }
        }

        if (empty($submittedToken) || empty($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }

        $valid = hash_equals($_SESSION[CSRF_TOKEN_NAME], $submittedToken);

        if ($valid && $regenerate) {
            self::regenerate();
        }

        return $valid;
    }

    /**
     * Check CSRF token without regenerating it (ideal for AJAX / autosave)
     */
    public static function check(): bool
    {
        return self::validate(false);
    }

    /**
     * Regenerate the CSRF token
     */
    public static function regenerate(): void
    {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
}
