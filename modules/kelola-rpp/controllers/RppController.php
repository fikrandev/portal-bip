<?php
/**
 * RppController (Legacy Redirect to Perangkat Pembelajaran)
 */
require_once BASE_PATH . '/modules/kelola-perangkat-pembelajaran/controllers/PerangkatController.php';

class RppController
{
    public static function index(): void
    {
        Response::redirect(url('kelola-perangkat-pembelajaran'));
    }
}
