<?php
/*
Plugin Name: Aula Booking System
Description: Sistema di prenotazione aule per WordPress
Version: 1.0
Author: Thomas Di Pasquale
*/

// Impedisci l'accesso diretto al file
if (!defined('ABSPATH')) {
    exit;
}

// Include il file di configurazione e connessione al database
require_once plugin_dir_path(__FILE__) . 'includes/config.php';

// Include il file di gestione delle prenotazioni
require_once plugin_dir_path(__FILE__) . 'includes/booking-handler.php';

// Include il file di gestione dell'interfaccia utente
require_once plugin_dir_path(__FILE__) . 'includes/frontend.php';

// Include il file di registrazione delle tabelle
require_once plugin_dir_path(__FILE__) . 'includes/database.php';

// Registrazione delle tabelle al primo installazione plugin
register_activation_hook(__FILE__, 'aula_booking_install');

// Aggiungi il menu all'amministrazione di WordPress
add_action('admin_menu', 'aula_booking_admin_menu');

function aula_booking_admin_menu() {
    add_menu_page(
        'Aula Booking System',
        'Aula Booking',
        'manage_options',
        'aula-booking-system',
        'aula_booking_admin_page',
        'dashicons-calendar-alt',
        6
    );
    
    add_submenu_page(
        'aula-booking-system',
        'Gestione Aule',
        'Gestione Aule',
        'manage_options',
        'aula-booking-aule',
        'aula_booking_aule_page'
    );
    
    add_submenu_page(
        'aula-booking-system',
        'Gestione Prenotazioni',
        'Gestione Prenotazioni',
        'manage_options',
        'aula-booking-prenotazioni',
        'aula_booking_prenotazioni_page'
    );
}

function aula_booking_admin_page() {
    // Codice per l'interfaccia amministrativa principale
    ?>
    <div class="wrap">
        <h1>Aula Booking System</h1>
        <div class="card">
            <h2>Dashboard</h2>
            <p>Benvenuto nel sistema di prenotazione aule.</p>
            <p>Utilizza il menu laterale per gestire aule e prenotazioni.</p>
        </div>
    </div>
    <?php
}

function aula_booking_aule_page() {
    // Codice per la gestione delle aule
    ?>
    <div class="wrap">
        <h1>Gestione Aule</h1>
        <!-- Implementare qui la gestione delle aule -->
    </div>
    <?php
}

function aula_booking_prenotazioni_page() {
    // Codice per la gestione delle prenotazioni
    ?>
    <div class="wrap">
        <h1>Gestione Prenotazioni</h1>
        <!-- Implementare qui la gestione delle prenotazioni -->
    </div>
    <?php
}