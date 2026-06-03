  var sqPendingFormId = null;

    function sqOpenModal(shipmentId) {
        sqPendingFormId = shipmentId;
        document.getElementById('sq-shipment-modal').classList.add('active');
    }

    function sqCloseModal() {
        document.getElementById('sq-shipment-modal').classList.remove('active');
        sqPendingFormId = null;
    }

    document.getElementById('sq-modal-confirm-btn').addEventListener('click', function() {
        if (sqPendingFormId !== null) {
            var form = document.getElementById('sq-form-' + sqPendingFormId);
            if (form) {
                var btn = document.createElement('input');
                btn.type = 'hidden';
                btn.name = 'createShipment';
                btn.value = '1';
                form.appendChild(btn);
                form.submit();
            }
        }
        sqCloseModal();
    });

    document.getElementById('sq-shipment-modal').addEventListener('click', function(e) {
        if (e.target === this) sqCloseModal();
    });