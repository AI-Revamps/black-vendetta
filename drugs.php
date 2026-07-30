<?php
/**
 * Drugshandel. De logica staat in inc/handel.php, gedeeld met drank.php.
 */

declare(strict_types=1);

require __DIR__ . '/inc/bootstrap.php';
require BV_INC . '/handel.php';

handel_pagina('drugs');
