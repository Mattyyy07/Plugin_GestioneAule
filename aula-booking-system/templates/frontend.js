document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Time slot booking
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.addEventListener('click', function() {
            const aulaId = this.dataset.aulaId;
            const ora = this.dataset.ora;
            const orario = this.dataset.orario;
            const aulaName = this.parentElement.parentElement.querySelector('h5').textContent;
            const dataSelezionata = document.querySelector('input[name="data"]').value;

            // Check if date is in the past
            const selectedDate = new Date(dataSelezionata);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate < today) {
                alert("Non è possibile prenotare per date passate!");
                return;
            }

            // Show booking modal
            document.getElementById('quickBookAulaId').value = aulaId;
            document.getElementById('quickBookData').value = dataSelezionata;
            document.getElementById('quickBookOra').value = ora;
            document.getElementById('quickBookAulaName').value = aulaName;
            document.getElementById('quickBookOrario').value = orario;

            const modal = new bootstrap.Modal(document.getElementById('quickBookModal'));
            modal.show();
        });
    });

    // Quick booking form submission
    document.getElementById('quickBookForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'prenota_aula');
        formData.append('security', aulaBooking.nonce);

        fetch(aulaBooking.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(response => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('quickBookModal'));
            modal.hide();

            if (response.success) {
                const toast = new bootstrap.Toast(document.getElementById('bookingSuccessToast'));
                toast.show();
                window.location.reload();
            } else {
                const toast = new bootstrap.Toast(document.getElementById('bookingErrorToast'));
                document.querySelector('#bookingErrorToast .toast-body').textContent = response.message;
                toast.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });

    // Cancel booking
    document.querySelectorAll('.cancel-booking').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('Sei sicuro di voler cancellare questa prenotazione?')) {
                const bookingId = this.dataset.bookingId;
                
                fetch(aulaBooking.ajaxUrl, {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'cancella_prenotazione',
                        security: aulaBooking.nonce,
                        booking_id: bookingId
                    })
                })
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        alert('Prenotazione cancellata con successo!');
                        window.location.reload();
                    } else {
                        alert('Errore durante la cancellazione: ' + response.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
    });
});