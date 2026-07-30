<?php
/**
 * Uitloggen.
 *
 * Alleen via POST met een geldig CSRF-token. In de oude versie was dit een
 * gewone link (login.php?x=logout), waardoor een afbeelding in een forumbericht
 * of privébericht iedereen die hem bekeek kon uitloggen.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';

if (is_post() && csrf_valid()) {
    auth_logout();
    session_boot();                 // nieuwe, lege sessie voor de melding
    flash('Je bent uitgelogd.', 'ok');
    redirect('index.php');
}

// Zonder geldige POST: geen actie, wel een knop om het alsnog te doen.
layout_header('Uitloggen');
panel_open('Uitloggen');

if (is_logged_in()) {
    echo '<p>Weet je zeker dat je wilt uitloggen?</p>';
    echo '<form method="post">' . csrf_field()
       . '<button type="submit">Ja, uitloggen</button> '
       . '<a class="knop" href="' . e(url('home.php')) . '">Nee, terug</a></form>';
} else {
    echo '<p>Je bent niet ingelogd. <a href="' . e(url('login.php')) . '">Inloggen</a></p>';
}

panel_close();
layout_footer();
