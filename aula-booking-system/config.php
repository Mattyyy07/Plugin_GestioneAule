<?php
// Impedisci l'accesso diretto al file
if (!defined('ABSPATH')) {
    exit;
}

// Usa direttamente le costanti di WordPress
define('DB_SERVER', DB_HOST);
define('DB_USERNAME', DB_USER);
define('DB_PASSWORD', DB_PASSWORD);
define('DB_NAME', DB_NAME);

global $wpdb;
$table_prefix = $wpdb->prefix;

define('AULE_TABLE', $table_prefix . 'aule');
define('PRENOTAZIONI_TABLE', $table_prefix . 'prenotazioni');
define('UTENTI_TABLE', $wpdb->users); // Usa direttamente la tabella WordPress users

// Funzioni di utilità
function isLoggato() {
    return is_user_logged_in();
}

function isAdmin() {
    return current_user_can('manage_options');
}

function checkAdmin() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Non hai i permessi necessari.'));
    }
}