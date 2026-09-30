<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Outbound Message Branding
    |--------------------------------------------------------------------------
    |
    | The signing block every outbound WhatsApp message closes with. Kept here
    | so rebranding the unit that sends these messages is a config change.
    |
    */

    'whatsapp' => [
        'unit_name' => env('WHATSAPP_UNIT_NAME', 'Unit Pelayanan Bimbingan Konseling'),
        'institution' => env('WHATSAPP_INSTITUTION', 'Universitas Negeri Gorontalo'),
    ],

];
