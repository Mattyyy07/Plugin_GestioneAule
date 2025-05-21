<?php
// Impedisci l'accesso diretto al file
if (!defined('ABSPATH')) {
    exit;
}

function aula_booking_install() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS " . AULE_TABLE . " (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nome varchar(50) NOT NULL,
        capienza int(11) NOT NULL,
        ha_proiettore tinyint(1) DEFAULT 0,
        ha_lim tinyint(1) DEFAULT 0,
        note text DEFAULT NULL,
        data_creazione timestamp NOT NULL DEFAULT current_timestamp(),
        ha_computer tinyint(1) DEFAULT 0,
        numero_computer int(11) DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS " . PRENOTAZIONI_TABLE . " (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        aula_id int(11) DEFAULT NULL,
        utente_id int(11) DEFAULT NULL,
        nome_classe varchar(50) DEFAULT NULL,
        materia varchar(100) DEFAULT NULL,
        data_prenotazione date DEFAULT NULL,
        ora_lezione int(11) DEFAULT NULL,
        data_creazione timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY  (id),
        KEY aula_data_ora (aula_id, data_prenotazione, ora_lezione)
    ) $charset_collate;";

    dbDelta($sql);
    
    // Inseriamo alcune aule di esempio se la tabella è vuota
    $count = $wpdb->get_var("SELECT COUNT(*) FROM " . AULE_TABLE);
    
    if ($count == 0) {
        $wpdb->insert(
            AULE_TABLE,
            array(
                'nome' => 'Aula 1',
                'capienza' => 30,
                'ha_proiettore' => 1,
                'ha_lim' => 1,
                'note' => 'Aula principale',
                'ha_computer' => 1,
                'numero_computer' => 15
            )
        );
        
        $wpdb->insert(
            AULE_TABLE,
            array(
                'nome' => 'Aula 2',
                'capienza' => 25,
                'ha_proiettore' => 1,
                'ha_lim' => 0,
                'note' => 'Aula secondaria',
                'ha_computer' => 0,
                'numero_computer' => 0
            )
        );
        
        $wpdb->insert(
            AULE_TABLE,
            array(
                'nome' => 'Laboratorio Informatica',
                'capienza' => 20,
                'ha_proiettore' => 1,
                'ha_lim' => 1,
                'note' => 'Laboratorio con computer',
                'ha_computer' => 1,
                'numero_computer' => 20
            )
        );
    }
}