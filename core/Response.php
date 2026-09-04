<?php
/**
 * Portal BIP - Response Helper
 * 
 * Utility methods for HTTP responses, redirects, and flash messages.
 */

class Response
{
    /**
     * Send a JSON response
     */
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirect to a URL
     */
    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Redirect back to the previous page
     */
    public static function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/dashboard';
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Redirect with a success flash message
     */
    public static function withSuccess(string $url, string $message): void
    {
        $_SESSION['flash_success'] = $message;
        self::redirect($url);
    }

    /**
     * Redirect with an error flash message
     */
    public static function withError(string $url, string $message): void
    {
        $_SESSION['flash_error'] = $message;
        self::redirect($url);
    }

    /**
     * Redirect with a warning flash message
     */
    public static function withWarning(string $url, string $message): void
    {
        $_SESSION['flash_warning'] = $message;
        self::redirect($url);
    }

    /**
     * Redirect back with validation errors and old input
     */
    public static function backWithErrors(array $errors, array $oldInput = []): void
    {
        $_SESSION['validation_errors'] = $errors;
        $_SESSION['old_input'] = $oldInput;
        self::back();
    }

    /**
     * Get and clear flash message
     */
    public static function flash(string $type): ?string
    {
        $key = 'flash_' . $type;
        $message = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return $message;
    }

    /**
     * Get and clear validation errors
     */
    public static function validationErrors(): array
    {
        $errors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);
        return $errors;
    }

    /**
     * Get and clear old input
     */
    public static function oldInput(string $field, mixed $default = ''): string
    {
        $value = $_SESSION['old_input'][$field] ?? ($default ?? '');
        if (isset($_SESSION['old_input'][$field])) {
            // Don't unset individual fields; clear all at once
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clear all old input
     */
    public static function clearOldInput(): void
    {
        unset($_SESSION['old_input']);
    }
}
