jQuery(document).ready(function($) {
    var tiempoRestante = $('.temporizador-descuento').data('tiempo-restante');
    
    if (tiempoRestante > 0) {
        setInterval(function() {
            var horas = Math.floor(tiempoRestante / 3600);
            var minutos = Math.floor((tiempoRestante % 3600) / 60);
            var segundos = tiempoRestante % 60;
            
            if (horas <= 9) horas = '0' + horas;
            if (minutos <= 9) minutos = '0' + minutos;
            if (segundos <= 9) segundos = '0' + segundos;

            $('.temporizador-descuento').html(`
                <p class="plan-title">¡Aprovecha la Promo!</p>
                <div class="counter-container-especial">
                    <div class="elementContainer">
                        <div class="counterElement">${horas}</div>
                        <div class="infoElement">Horas</div>
                    </div>
                    <div class="elementContainer">
                        <div class="counterElement">${minutos}</div>
                        <div class="infoElement">Min</div>
                    </div>
                    <div class="elementContainer">
                        <div class="counterElement">${segundos}</div>
                        <div class="infoElement">Seg</div>
                    </div>
                </div>
            `);

            tiempoRestante--;
        }, 1000);
    } else {
        $('.temporizador-descuento').html('El descuento ha expirado.');
    }
});
