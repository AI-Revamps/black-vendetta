/* Black Vendetta - kleine hulpjes in de browser.
   Geen bibliotheken; de site werkt volledig zonder JavaScript. */

(function () {
    'use strict';

    // --- Menu-la op smalle schermen -------------------------------------
    //
    // Twee knoppen openen hetzelfde menu: die in de kopbalk en die in de
    // onderbalk. De overlay erachter vangt een klik naast het menu op.

    var menu    = document.getElementById('zijmenu');
    var overlay = document.querySelector('.menu-overlay');
    var knoppen = document.querySelectorAll('.menu-toggle, .menu-toggle-onder');

    function zetMenu(open) {
        if (!menu) { return; }

        menu.classList.toggle('open', open);

        if (overlay) {
            overlay.classList.toggle('open', open);
            overlay.hidden = !open;
        }

        knoppen.forEach(function (knop) {
            knop.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        // Achtergrond niet mee laten scrollen zolang de la openstaat.
        document.body.style.overflow = open ? 'hidden' : '';
    }

    if (menu && knoppen.length > 0) {
        knoppen.forEach(function (knop) {
            knop.addEventListener('click', function () {
                zetMenu(!menu.classList.contains('open'));
            });
        });

        if (overlay) {
            overlay.addEventListener('click', function () { zetMenu(false); });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && menu.classList.contains('open')) {
                zetMenu(false);
            }
        });

        // Een link aanklikken sluit de la; anders blijft hij openstaan
        // terwijl de nieuwe pagina eronder laadt.
        menu.addEventListener('click', function (e) {
            if (e.target.closest('a')) { zetMenu(false); }
        });
    }

    // --- Schaduw bij tabellen die verder doorlopen -----------------------
    //
    // Een tabel die breder is dan het scherm scrollt horizontaal, maar dat is
    // niet te zien. De schaduw rechts laat zien dát er meer staat en verdwijnt
    // zodra je aan het eind bent.

    var wikkels = document.querySelectorAll('.tabelwikkel');

    function schaduw(wikkel) {
        var meer = wikkel.scrollWidth - wikkel.clientWidth - wikkel.scrollLeft > 4;
        wikkel.classList.toggle('meer-rechts', meer);
    }

    wikkels.forEach(function (wikkel) {
        schaduw(wikkel);
        wikkel.addEventListener('scroll', function () { schaduw(wikkel); }, { passive: true });
    });

    if (wikkels.length > 0) {
        window.addEventListener('resize', function () {
            wikkels.forEach(schaduw);
        }, { passive: true });
    }

    // --- Aftellers -------------------------------------------------------
    //
    // Elk element met data-tot="<unix-tijd>" telt af naar nul en laadt de
    // pagina daarna één keer opnieuw, zodat de knop weer werkt.

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
