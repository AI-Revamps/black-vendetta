<?php
/**
 * Opmaak van tekst die spelers zelf schrijven: forumberichten, privéberichten,
 * profielteksten en familie-informatie.
 *
 * De werkwijze is: eerst alles onschadelijk maken met e(), daarna alleen de
 * opmaak die we uitdrukkelijk toestaan weer terugzetten. Nooit andersom.
 *
 * Wat de oude versie deed en waarom dat misging:
 *
 *  - forum.php zette [img]...[/img] rechtstreeks om naar <img src="...">
 *    zonder de inhoud te controleren. Met [img]x" onerror="alert(1)[/img] liep
 *    er dus JavaScript bij iedereen die het topic opende.
 *  - forum.php toonde berichten met nl2br(stripslashes(...)) - zonder enige
 *    escaping. Elke HTML-tag in een bericht werd uitgevoerd.
 *  - message.php zette [inc]...[/inc] om naar <iframe src=...>. Daarmee kon
 *    iedereen een willekeurige externe pagina in andermans postvak tonen: een
 *    kant-en-klare manier om een nagemaakt inlogscherm voor te schotelen. Die
 *    tag is hier bewust niet overgenomen.
 *  - De enige filtering was preg_replace('/</','&#60;'). Die verving alleen het
 *    kleiner-dan-teken; aanhalingstekens en ampersands bleven staan, dus
 *    attributen waren nog steeds te openen. Bovendien gebeurde het bij het
 *    opslaan, waardoor alles wat al in de database stond ongefilterd bleef.
 */

declare(strict_types=1);

defined('BV_INC') || exit;

/**
 * Zet spelerstekst om naar veilige HTML.
 *
 * @param string $ruw     De tekst zoals die in de database staat.
 * @param array  $opties  'plaatjes' => afbeeldingen toestaan (standaard true),
 *                        'spelacties' => [invite]- en [ws]-knoppen (standaard false)
 */
function bericht_html(string $ruw, array $opties = []): string
{
    $plaatjes   = $opties['plaatjes']   ?? true;
    $spelacties = $opties['spelacties'] ?? false;

    // Stap 1: alles onschadelijk maken. Hierna kan er geen HTML meer ontstaan
    // die wij niet zelf terugzetten.
    $html = e($ruw);

    // Stap 2: toegestane opmaak.
    $html = opmaak_eenvoudig($html);
    $html = opmaak_kleur($html);
    $html = opmaak_grootte($html);
    $html = opmaak_lijst($html);
    $html = opmaak_citaat($html);
    $html = opmaak_url($html);

    if ($plaatjes) {
        $html = opmaak_plaatje($html);
    }
    if ($spelacties) {
        $html = opmaak_spelacties($html);
    }

    $html = opmaak_smilies($html);
    $html = opmaak_autolink($html);

    return nl2br($html, false);
}

/** Korte variant voor tekst zonder opmaak, zoals een profielregel. */
function tekst_html(string $ruw): string
{
    return nl2br(e($ruw), false);
}

// --- Losse omzettingen -------------------------------------------------------

function opmaak_eenvoudig(string $html): string
{
    $paren = [
        'b'     => 'strong',
        'i'     => 'em',
        'u'     => 'u',
        's'     => 's',
        'small' => 'small',
    ];

    foreach ($paren as $tag => $element) {
        $html = preg_replace(
            '#\[' . $tag . '\](.*?)\[/' . $tag . '\]#is',
            '<' . $element . '>$1</' . $element . '>',
            $html
        );
    }

    return $html;
}

/** [color=rood] of [color=#ff9900] - alleen veilige waarden. */
function opmaak_kleur(string $html): string
{
    return preg_replace_callback(
        '#\[color=([a-z]{3,20}|\#[0-9a-f]{3,6})\](.*?)\[/color\]#is',
        static function (array $m): string {
            $kleur = strtolower($m[1]);

            // Alleen een hexwaarde of een naam uit deze lijst. Zo kan er geen
            // url(javascript:...) of expression() in de stijl belanden.
            $namen = ['rood' => 'red', 'blauw' => 'blue', 'groen' => 'green',
                      'geel' => 'yellow', 'wit' => 'white', 'zwart' => 'black',
                      'oranje' => 'orange', 'paars' => 'purple', 'grijs' => 'gray',
                      'roze' => 'pink', 'bruin' => 'brown'];

            if (isset($namen[$kleur])) {
                $kleur = $namen[$kleur];
            } elseif (!preg_match('/^\#[0-9a-f]{3}([0-9a-f]{3})?$/', $kleur)) {
                return $m[2];   // onbekende kleur: laat de tekst zoals hij is
            }

            return '<span style="color:' . $kleur . '">' . $m[2] . '</span>';
        },
        $html
    ) ?? $html;
}

/** [size=1] tot [size=7], omgerekend naar een beperkt bereik. */
function opmaak_grootte(string $html): string
{
    return preg_replace_callback(
        '#\[size=([1-7])\](.*?)\[/size\]#is',
        static function (array $m): string {
            $factor = round(0.7 + ((int) $m[1] - 1) * 0.15, 2);
            return '<span style="font-size:' . $factor . 'em">' . $m[2] . '</span>';
        },
        $html
    ) ?? $html;
}

function opmaak_lijst(string $html): string
{
    return preg_replace_callback(
        '#\[list\](.*?)\[/list\]#is',
        static function (array $m): string {
            $regels = preg_split('/\R|\[\*\]/', $m[1], -1, PREG_SPLIT_NO_EMPTY);
            $items  = '';
            foreach ($regels as $regel) {
                $regel = trim($regel);
                if ($regel !== '') {
                    $items .= '<li>' . $regel . '</li>';
                }
            }
            return $items === '' ? '' : '<ul>' . $items . '</ul>';
        },
        $html
    ) ?? $html;
}

function opmaak_citaat(string $html): string
{
    return preg_replace('#\[quote\](.*?)\[/quote\]#is', '<blockquote>$1</blockquote>', $html) ?? $html;
}

/** [url]adres[/url] en [url=adres]tekst[/url]. */
function opmaak_url(string $html): string
{
    $html = preg_replace_callback(
        '#\[url=([^\]\s]+)\](.*?)\[/url\]#is',
        static function (array $m): string {
            $adres = veilige_url($m[1]);
            return $adres === null ? $m[2] : link_html($adres, $m[2]);
        },
        $html
    ) ?? $html;

    return preg_replace_callback(
        '#\[url\]([^\[\s]+)\[/url\]#is',
        static function (array $m): string {
            $adres = veilige_url($m[1]);
            return $adres === null ? $m[1] : link_html($adres, e($adres));
        },
        $html
    ) ?? $html;
}

function opmaak_plaatje(string $html): string
{
    return preg_replace_callback(
        '#\[img\]([^\[\s]+)\[/img\]#is',
        static function (array $m): string {
            $adres = veilige_url($m[1]);

            if ($adres === null) {
                return '';
            }

            return '<img src="' . e($adres) . '" alt="" loading="lazy" '
                 . 'style="max-width:100%;height:auto;border-radius:4px">';
        },
        $html
    ) ?? $html;
}

/** Knoppen die alleen in privéberichten van het systeem voorkomen. */
function opmaak_spelacties(string $html): string
{
    $html = preg_replace_callback(
        '#\[invite\]([A-Za-z0-9_\- ]{1,20})\[/invite\]#',
        static fn (array $m): string =>
            '<a class="knop" href="' . e(url('invite.php?fam=' . rawurlencode($m[1]))) . '">'
            . 'Uitnodiging bekijken</a>',
        $html
    ) ?? $html;

    return preg_replace_callback(
        '#\[ws\](\d{1,10})\[/ws\]#',
        static fn (array $m): string =>
            '<a class="knop" href="' . e(url('mshop.php?x=ws&page=sell')) . '">'
            . 'Verklaring verkopen</a>',
        $html
    ) ?? $html;
}

function opmaak_smilies(string $html): string
{
    static $lijst = null;

    if ($lijst === null) {
        $lijst = [
            ':)'  => 'smilie1.gif',
            ':-)' => 'smilie1.gif',
            ':('  => 'smilie2.gif',
            ':-(' => 'smilie2.gif',
            ';)'  => 'smilie3.gif',
            ':D'  => 'smilie4.gif',
            ':p'  => 'smilie5.gif',
            ':o'  => 'smilie6.gif',
            '8)'  => 'smilie7.gif',
            ':|'  => 'smilie8.gif',
            ':s'  => 'smilie9.gif',
            ':x'  => 'smilie10.gif',
            '>:('  => 'smilie11.gif',
        ];
    }

    foreach ($lijst as $teken => $bestand) {
        // De tekens zijn na e() deels omgezet (> werd &gt;), dus we zoeken op
        // de geëscapete vorm.
        $zoek = e($teken);
        $html = str_replace(
            $zoek,
            '<img src="' . e(url('smilies/' . $bestand)) . '" alt="' . $zoek . '" class="smilie">',
            $html
        );
    }

    return $html;
}

/** Losse webadressen in de tekst klikbaar maken. */
function opmaak_autolink(string $html): string
{
    return preg_replace_callback(
        '#(?<![">=/])\bhttps?://[^\s<]{4,200}#i',
        static function (array $m): string {
            $adres = veilige_url($m[0]);
            return $adres === null ? $m[0] : link_html($adres, e(afkorten($adres)));
        },
        $html
    ) ?? $html;
}

// --- Hulpjes ------------------------------------------------------------------

/**
 * Controleer een adres uit spelerstekst.
 *
 * Geeft het schone adres terug, of null als het niet door de beugel kan.
 * Alleen http en https: daarmee vallen javascript:, data: en vbscript: af.
 */
function veilige_url(string $ruw): ?string
{
    // De tekst is al door e() gegaan, dus entiteiten eerst terugdraaien.
    $adres = html_entity_decode($ruw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $adres = trim($adres);

    if ($adres === '' || strlen($adres) > 500) {
        return null;
    }

    // Tekens die een attribuut kunnen afsluiten of een nieuwe tag beginnen.
    if (preg_match('/["\'<>\s`]/', $adres)) {
        return null;
    }

    $schema = strtolower((string) parse_url($adres, PHP_URL_SCHEME));

    if (!in_array($schema, ['http', 'https'], true)) {
        return null;
    }

    return filter_var($adres, FILTER_VALIDATE_URL) === false ? null : $adres;
}

function link_html(string $adres, string $tekst): string
{
    // noopener en noreferrer: de doelpagina krijgt geen greep op ons venster.
    return '<a href="' . e($adres) . '" target="_blank" rel="noopener noreferrer nofollow">'
         . $tekst . '</a>';
}

function afkorten(string $tekst, int $max = 60): string
{
    return mb_strlen($tekst) <= $max ? $tekst : mb_substr($tekst, 0, $max - 1) . '…';
}
