# Autoafbeeldingen

Deze map hoort de foto's te bevatten die `install/schema.sql` voor de
autocatalogus (tabel `cars`) verwacht. Het zijn beeldbestanden, geen code, dus
ze staan niet in deze repository — voeg ze hier zelf toe.

Nodig, exact deze bestandsnamen (kleine letters, zoals in `schema.sql`):

```
alfa_romeo_fnm_2000_jk.jpg
aston_martin_db3s.jpg
buick_cabrio.jpg
caddillac_sedan.jpg
chevy_camaro_ss.jpg
chrysler_converible.jpg
dodge_dart.jpg
dodge_kingsway.jpg
ford_fairlane.jpg
ford_mustang.jpg
mercury_cougar.jpg
pontiac.jpg
pontiac_bonneville.jpg
porsche_356b_cabriolet.jpg
streamliner.jpg
```

Ontbreekt een bestand, dan toont `nickacar.php` na een geslaagde autodiefstal
gewoon geen plaatje — geen kapot plaatje-icoon. Zodra het bestand hier staat,
verschijnt het vanzelf; er hoeft verder niets aangepast te worden.

Wil je andere auto's of andere bestandsnamen? Pas dan de kolom `url` in de
tabel `cars` aan (via `adm-items.php` of rechtstreeks in de database) zodat
hij naar het juiste bestand in deze map wijst.
