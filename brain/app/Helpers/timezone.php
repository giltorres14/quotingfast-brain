<?php

/**
 * Get current time in EST/EDT timezone
 */
function estNow() {
    return \Carbon\Carbon::now('America/New_York');
}

/**
 * Convert UTC to EST/EDT
 */
function toEst($datetime) {
    if (!$datetime) return null;
    return \Carbon\Carbon::parse($datetime)->setTimezone('America/New_York');
}

/**
 * Format date in EST/EDT
 */
function formatEst($datetime, $format = 'm/d/Y g:i A') {
    if (!$datetime) return null;
    return \Carbon\Carbon::parse($datetime)->setTimezone('America/New_York')->format($format);
}