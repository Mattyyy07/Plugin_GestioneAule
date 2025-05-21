<?php
/**
 * Aula Booking System - Frontend Interface
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue necessary scripts and styles
function aula_booking_enqueue_scripts() {
    wp_enqueue_style('aula-booking-frontend', plugins_url('css/frontend.css', __FILE__));
    wp_enqueue_script('aula-booking-frontend', plugins_url('js/frontend.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('aula-booking-frontend', 'aulaBooking', array(
        'nonce' => wp_create_nonce('aula_booking_nonce'),
        'ajaxUrl' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'aula_booking_enqueue_scripts');

function aula_booking_frontend() {
    // Get current user
    $current_user = wp_get_current_user();
    
    // Get selected date from GET or use today
    $data_selezionata = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
    
    // Define time slots
    $orari_diurno = [
        '1' => '8.10-9.05',
        '2' => '9.05-10.00',
        '3' => '10.10-11.05',
        '4' => '11.05-12.00',
        '5' => '12.10-13.05',
        '6' => '13.05-14.00',
        '7' => '14.20-15.15',
        '8' => '15.15-16.10'
    ];

    $orari_serale = [
        '9' => '18.00-18.50',
        '10' => '18.50-19.50',
        '11' => '19.50-20.40',
        '12' => '20.40-21.30'
    ];

    // Get aule from database
    global $wpdb;
    $aula_query = $wpdb->get_results("SELECT * FROM " . AULE_TABLE);

    // Get existing bookings for selected date
    $bookings = [];
    if ($aula_query) {
        foreach ($aula_query as $aula) {
            foreach ($orari_diurno as $ora => $orario) {
                $is_booked = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*) FROM " . PRENOTAZIONI_TABLE . "
                        WHERE aula_id = %d AND data_prenotazione = %s AND ora_lezione = %d",
                        $aula->id,
                        $data_selezionata,
                        $ora
                    )
                );
                if ($is_booked) {
                    $booking = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT p.*, u.nome FROM " . PRENOTAZIONI_TABLE . " p
                            LEFT JOIN " . UTENTI_TABLE . " u ON p.utente_id = u.id
                            WHERE p.aula_id = %d AND p.data_prenotazione = %s AND p.ora_lezione = %d",
                            $aula->id,
                            $data_selezionata,
                            $ora
                        )
                    );
                    $bookings[$aula->id][$ora] = $booking;
                }
            }
            foreach ($orari_serale as $ora => $orario) {
                $is_booked = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*) FROM " . PRENOTAZIONI_TABLE . "
                        WHERE aula_id = %d AND data_prenotazione = %s AND ora_lezione = %d",
                        $aula->id,
                        $data_selezionata,
                        $ora
                    )
                );
                if ($is_booked) {
                    $booking = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT p.*, u.nome FROM " . PRENOTAZIONI_TABLE . " p
                            LEFT JOIN " . UTENTI_TABLE . " u ON p.utente_id = u.id
                            WHERE p.aula_id = %d AND p.data_prenotazione = %s AND p.ora_lezione = %d",
                            $aula->id,
                            $data_selezionata,
                            $ora
                        )
                    );
                    $bookings[$aula->id][$ora] = $booking;
                }
            }
        }
    }
    
    // Display frontend interface
    ?>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="bi bi-calendar3 me-2"></i>
                Aule Disponibili - <?php echo date('d/m/Y', strtotime($data_selezionata)); ?>
            </h2>
            <div class="date-selector">
                <form class="d-flex gap-2" method="GET">
                    <input type="date" name="data" class="form-control" value="<?php echo esc_attr($data_selezionata); ?>">
                    <button type="submit" class="btn btn-primary btn-prenota">
                        <i class="bi bi-search me-1"></i>
                        Cerca
                    </button>
                </form>
            </div>
        </div>

        <div class="schedule-section mb-4">
            <div class="schedule-type-indicator">
                <i class="bi bi-sun-fill me-2"></i>
                Orario Diurno
            </div>
            <div class="row">
                <?php foreach ($aula_query as $aula) : ?>
                    <div class="col-md-6 mb-4">
                        <div class="room-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?php echo esc_html($aula->nome); ?></h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="feature-badge">
                                            <i class="bi bi-people-fill me-1"></i>
                                            <?php echo esc_html($aula->capienza); ?> posti
                                        </span>
                                        <?php if ($aula->ha_computer) : ?>
                                        <span class="feature-badge">
                                            <i class="bi bi-pc-display me-1"></i>
                                            <?php echo esc_html($aula->numero_computer); ?> PC
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($aula->ha_proiettore) : ?>
                                        <span class="feature-badge">
                                            <i class="bi bi-projector me-1"></i>
                                            Proiettore
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($aula->ha_lim) : ?>
                                        <span class="feature-badge">
                                            <i class="bi bi-display me-1"></i>
                                            LIM
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <a href="#" class="btn btn-primary btn-prenota">
                                    <i class="bi bi-calendar-plus me-1"></i>
                                    Prenota
                                </a>
                            </div>

                            <div class="d-flex flex-wrap">
                                <?php foreach ($orari_diurno as $ora => $orario) : ?>
                                    <?php
                                    $is_booked = isset($bookings[$aula->id][$ora]) ? true : false;
                                    $booking = $is_booked ? $bookings[$aula->id][$ora] : null;
                                    ?>
                                    <div class="time-slot rounded <?php echo $is_booked ? 'bg-danger' : 'bg-success'; ?> text-white"
                                        data-aula-id="<?php echo esc_attr($aula->id); ?>"
                                        data-ora="<?php echo esc_attr($ora); ?>"
                                        data-orario="<?php echo esc_attr($orario); ?>">
                                        <i class="bi <?php echo $is_booked ? 'bi-x-circle' : 'bi-check-circle'; ?> me-1"></i>
                                        <?php echo esc_html($orario); ?>
                                        <?php if ($is_booked) : ?>
                                            <div class="booking-tooltip">
                                                <?php echo esc_html($booking->nome_classe); ?> - 
                                                <?php echo esc_html($booking->materia); ?> - 
                                                Prof. <?php echo esc_html($booking->nome); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="schedule-section">
            <div class="schedule-type-indicator">
                <i class="bi bi-moon-fill me-2"></i>
                Orario Serale
            </div>
            <div class="row">
                <?php foreach ($aula_query as $aula) : ?>
                    <div class="col-md-6 mb-4">
                        <div class="room-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?php echo esc_html($aula->nome); ?></h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="feature-badge">
                                            <i class="bi bi-people-fill me-1"></i>
                                            <?php echo esc_html($aula->capienza); ?> posti
                                        </span>
                                        <?php if ($aula->ha_computer) : ?>
                                        <span class="feature-badge">
                                            <i class="bi bi-pc-display me-1"></i>
                                            <?php echo esc_html($aula->numero_computer); ?> PC
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($aula->ha_proiettore) : ?>
                                        <span class="feature-badge">
                                            <i class="bi bi-projector me-1"></i>
                                            Proiettore
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($aula->ha_lim) : ?>
                                        <span class="feature-badge">
                                            <i class="bi bi-display me-1"></i>
                                            LIM
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <a href="#" class="btn btn-primary btn-prenota">
                                    <i class="bi bi-calendar-plus me-1"></i>
                                    Prenota
                                </a>
                            </div>

                            <div class="d-flex flex-wrap">
                                <?php foreach ($orari_serale as $ora => $orario) : ?>
                                    <?php
                                    $is_booked = isset($bookings[$aula->id][$ora]) ? true : false;
                                    $booking = $is_booked ? $bookings[$aula->id][$ora] : null;
                                    ?>
                                    <div class="time-slot rounded <?php echo $is_booked ? 'bg-danger' : 'bg-success'; ?> text-white"
                                        data-aula-id="<?php echo esc_attr($aula->id); ?>"
                                        data-ora="<?php echo esc_attr($ora); ?>"
                                        data-orario="<?php echo esc_attr($orario); ?>">
                                        <i class="bi <?php echo $is_booked ? 'bi-x-circle' : 'bi-check-circle'; ?> me-1"></i>
                                        <?php echo esc_html($orario); ?>
                                        <?php if ($is_booked) : ?>
                                            <div class="booking-tooltip">
                                                <?php echo esc_html($booking->nome_classe); ?> - 
                                                <?php echo esc_html($booking->materia); ?> - 
                                                Prof. <?php echo esc_html($booking->nome); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Existing Bookings Table -->
        <div class="existing-bookings">
            <h3 class="mb-4">Le Tue Prenotazioni</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Aula</th>
                        <th>Data</th>
                        <th>Ora</th>
                        <th>Classe</th>
                        <th>Materia</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $user_bookings = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT p.*, a.nome as aula_nome FROM " . PRENOTAZIONI_TABLE . " p
                            LEFT JOIN " . AULE_TABLE . " a ON p.aula_id = a.id
                            WHERE p.utente_id = %d
                            ORDER BY p.data_prenotazione DESC",
                            $current_user->ID
                        )
                    );
                    if ($user_bookings) :
                        foreach ($user_bookings as $booking) :
                    ?>
                            <tr>
                                <td><?php echo esc_html($booking->aula_nome); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($booking->data_prenotazione)); ?></td>
                                <td>
                                    <?php
                                    $label = '';
                                    if ($booking->ora_lezione <= 8) {
                                        $label = $orari_diurno[$booking->ora_lezione];
                                    } elseif ($booking->ora_lezione >= 9 && $booking->ora_lezione <= 12) {
                                        $label = $orari_serale[$booking->ora_lezione - 8];
                                    }
                                    echo esc_html($label);
                                    ?>
                                </td>
                                <td><?php echo esc_html($booking->nome_classe); ?></td>
                                <td><?php echo esc_html($booking->materia); ?></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-danger cancel-booking" data-booking-id="<?php echo $booking->id; ?>">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center">Nessuna prenotazione trovata.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Booking Modal -->
    <div class="modal fade" id="quickBookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-plus me-2"></i>
                        Prenotazione Rapida
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="quickBookForm">
                    <div class="modal-body">
                        <input type="hidden" name="aula_id" id="quickBookAulaId">
                        <input type="hidden" name="data" id="quickBookData">
                        <input type="hidden" name="ora" id="quickBookOra">
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-building me-2"></i>
                                Aula
                            </label>
                            <input type="text" class="form-control" id="quickBookAulaName" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-clock me-2"></i>
                                Orario
                            </label>
                            <input type="text" class="form-control" id="quickBookOrario" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-people me-2"></i>
                                Classe
                            </label>
                            <input type="text" name="classe" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-book me-2"></i>
                                Materia
                            </label>
                            <input type="text" name="materia" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>
                            Annulla
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calendar-check me-2"></i>
                            Conferma Prenotazione
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Loading Spinner -->
    <div class="page-loader">
        <div class="loader-spinner"></div>
    </div>
    
    <!-- Modal for Success Message -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="bookingSuccessToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                <strong class="me-auto">Prenotazione</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Prenotazione effettuata con successo!
            </div>
        </div>
    </div>
    
    <!-- Modal for Error Message -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="bookingErrorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-exclamation-circle-fill text-danger me-2"></i>
                <strong class="me-auto">Prenotazione</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Si è verificato un errore durante la prenotazione.
            </div>
        </div>
    </div>
    <?php
}

// Add shortcode for frontend interface
add_shortcode('aula_booking', 'aula_booking_frontend');