<?php
add_action('wp_ajax_quick_booking', 'handle_quick_booking');
add_action('wp_ajax_nopriv_quick_booking', 'handle_quick_booking');

function handle_quick_booking() {
    check_ajax_referer('quick_booking_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Non autorizzato']);
    }

    $aula_id = intval($_POST['aula_id']);
    $data = sanitize_text_field($_POST['data']);
    $ora = intval($_POST['ora']);
    $classe = sanitize_text_field($_POST['classe']);
    $materia = sanitize_text_field($_POST['materia']);

    global $wpdb;

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

    // Inserisci la prenotazione
    $insert_sql = $wpdb->prepare(
        "INSERT INTO " . PRENOTAZIONI_TABLE . " 
        (aula_id, utente_id, nome_classe, materia, data_prenotazione, ora_lezione) 
        VALUES (%d, %d, %s, %s, %s, %d)",
        $aula_id,
        get_current_user_id(),
        $classe,
        $materia,
        $data,
        $ora
    );

    if ($wpdb->query($insert_sql)) {
        wp_send_json_success(['message' => 'Prenotazione effettuata con successo!']);
    } else {
        wp_send_json_error(['message' => 'Errore durante la prenotazione']);
    }
}