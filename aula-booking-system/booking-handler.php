<?php
// Impedisci l'accesso diretto al file
if (!defined('ABSPATH')) {
    exit;
}

// Gestione prenotazioni standard
add_action('wp_ajax_prenota_aula', 'handle_aula_booking');
add_action('wp_ajax_nopriv_prenota_aula', 'handle_aula_booking');

function handle_aula_booking() {
    check_ajax_referer('aula_booking_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato']);
    }

    $aula_id = intval($_POST['aula_id']);
    $data = sanitize_text_field($_POST['data']);
    $ora = intval($_POST['ora']);
    $classe = sanitize_text_field($_POST['classe']);
    $materia = sanitize_text_field($_POST['materia']);

    global $wpdb;

    // Verifica se la data è nel passato
    if (strtotime($data) < strtotime(date('Y-m-d'))) {
        wp_send_json_error(['message' => 'Non è possibile prenotare per date passate!']);
    }

    // Verifica se l'aula è già prenotata
    $check_sql = $wpdb->prepare(
        "SELECT COUNT(*) FROM " . PRENOTAZIONI_TABLE . " 
        WHERE aula_id = %d AND data_prenotazione = %s AND ora_lezione = %d",
        $aula_id,
        $data,
        $ora
    );

    $is_booked = $wpdb->get_var($check_sql);

    if ($is_booked > 0) {
        wp_send_json_error(['message' => 'L\'aula è già prenotata per questo orario']);
    }

    // Verifica prenotazioni esistenti dell'utente (solo per non admin)
    if (!current_user_can('manage_options')) {
        // Verifica altre prenotazioni nella stessa ora
        $check_user_sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM " . PRENOTAZIONI_TABLE . " 
            WHERE utente_id = %d AND data_prenotazione = %s AND ora_lezione = %d",
            get_current_user_id(),
            $data,
            $ora
        );

        $user_has_booking = $wpdb->get_var($check_user_sql);

        if ($user_has_booking > 0) {
            wp_send_json_error(['message' => 'Hai già una prenotazione per questo orario!']);
        }

        // Verifica limite giornaliero
        $check_daily_sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM " . PRENOTAZIONI_TABLE . " 
            WHERE utente_id = %d AND data_prenotazione = %s",
            get_current_user_id(),
            $data
        );

        $daily_bookings = $wpdb->get_var($check_daily_sql);

        if ($daily_bookings >= 4) {
            wp_send_json_error(['message' => 'Hai raggiunto il limite massimo di prenotazioni giornaliere!']);
        }
    }

    // Inserisci la prenotazione
    $insert_sql = $wpdb->insert(
        PRENOTAZIONI_TABLE,
        array(
            'aula_id' => $aula_id,
            'utente_id' => get_current_user_id(),
            'nome_classe' => $classe,
            'materia' => $materia,
            'data_prenotazione' => $data,
            'ora_lezione' => $ora
        )
    );

    if ($insert_sql) {
        wp_send_json_success(['message' => 'Prenotazione effettuata con successo!']);
    } else {
        wp_send_json_error(['message' => 'Errore durante la prenotazione: ' . $wpdb->last_error]);
    }
}

// Gestione cancellazione prenotazioni
add_action('wp_ajax_cancella_prenotazione', 'handle_cancella_prenotazione');
add_action('wp_ajax_nopriv_cancella_prenotazione', 'handle_cancella_prenotazione');

function handle_cancella_prenotazione() {
    check_ajax_referer('aula_booking_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato']);
    }

    $booking_id = intval($_POST['booking_id']);
    
    global $wpdb;
    
    // Verifica che la prenotazione appartenga all'utente corrente o che sia un amministratore
    if (!current_user_can('manage_options')) {
        $check_owner = $wpdb->prepare(
            "SELECT COUNT(*) FROM " . PRENOTAZIONI_TABLE . " 
            WHERE id = %d AND utente_id = %d",
            $booking_id,
            get_current_user_id()
        );
        
        $is_owner = $wpdb->get_var($check_owner);
        
        if ($is_owner == 0) {
            wp_send_json_error(['message' => 'Non sei autorizzato a cancellare questa prenotazione']);
        }
    }
    
    // Verifica se la prenotazione è nel passato
    $check_date = $wpdb->prepare(
        "SELECT data_prenotazione FROM " . PRENOTAZIONI_TABLE . " 
        WHERE id = %d",
        $booking_id
    );
    
    $booking_date = $wpdb->get_var($check_date);
    
    if (strtotime($booking_date) < strtotime(date('Y-m-d')) && !current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Non è possibile cancellare prenotazioni passate']);
    }
    
    // Cancella la prenotazione
    $result = $wpdb->delete(
        PRENOTAZIONI_TABLE,
        array('id' => $booking_id),
        array('%d')
    );
    
    if ($result) {
        wp_send_json_success(['message' => 'Prenotazione cancellata con successo']);
    } else {
        wp_send_json_error(['message' => 'Errore durante la cancellazione: ' . $wpdb->last_error]);
    }
}