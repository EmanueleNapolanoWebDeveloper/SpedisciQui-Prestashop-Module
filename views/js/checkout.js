document.addEventListener('change', function (e) {

    if (e.target.name === 'spedisciqui_selected_service') {

        console.log(
            'Servizio selezionato:',
            e.target.value
        );
    }
});