<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Prescription Dispense Enforcement
    |--------------------------------------------------------------------------
    |
    | Supported values:
    | - block: reject non-compliant dispense attempts
    | - warn: allow but write a warning audit event
    |
    */
    'dispense_enforcement' => env('RX_DISPENSE_ENFORCEMENT', 'block'),
];
