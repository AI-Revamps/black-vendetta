/* Black Vendetta - kleine hulpjes in de browser.
   Geen bibliotheken; de site werkt volledig zonder JavaScript. */

(function () {
    'use strict';

    // Menuknop op smalle schermen.
    var knop = document.querySelector('.menu-toggle');
    var menu = document.getElementById('zijmenu');

    if (knop && menu) {
        knop.addEventListener('click', function () {
            var open = menu.classList.toggle('open');
            knop.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    // Aftellers: elk element met data-tot="<unix-tijd>" telt af naar nul en
    // laadt de pagina daarna één keer opnieuw, zodat de knop weer werkt.
    var tellers = document.querySelectorAll('[data-tot]');
    if (tellers.length === 0) {
        return;
    }

    function opmaak(seconden) {
        if (seconden <= 0) { return '0:00'; }
        var u = Math.floor(seconden / 3600);
        var m = Math.floor((seconden % 3600) / 60);
        var s = seconden % 60;
        var mm = (u > 0 && m < 10 ? '0' : '') + m;
        return (u > 0 ? u + ':' : '') + mm + ':' + (s < 10 ? '0' : '') + s;
    }

    var herlaadGepland = false;

    function tik() {
        var nu = Math.floor(Date.now() / 1000);
        var actief = 0;

        tellers.forEach(function (el) {
            var over = parseInt(el.getAttribute('data-tot'), 10) - nu;
            if (over > 0) {
                el.textContent = opmaak(over);
                actief++;
            } else if (el.textContent !== '0:00') {
                el.textContent = '0:00';
                if (!herlaadGepland) {
                    herlaadGepland = true;
                    setTimeout(function () { location.reload(); }, 800);
                }
            }
        });

        if (actief > 0) {
            setTimeout(tik, 1000);
        }
    }

    tik();
}());
