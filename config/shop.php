<?php

return [
    'name' => 'Sizan Jastip',

    // Nomor diambil dari .env. Nama pemilik dan catatan boleh diubah di sini.
    'accounts' => [
        ['bank' => 'BCA',       'number' => env('SHOP_BCA'),       'name' => null,       'note' => null],
        ['bank' => 'SEABANK',   'number' => env('SHOP_SEABANK'),   'name' => null,       'note' => null],
        ['bank' => 'DANA',      'number' => env('SHOP_DANA'),      'name' => 'Siti A',   'note' => null],
        ['bank' => 'SHOPEEPAY', 'number' => env('SHOP_SHOPEEPAY'), 'name' => 'Endah',    'note' => 'username: haisizan'],
        ['bank' => 'ALADIN',    'number' => env('SHOP_ALADIN'),    'name' => 'Endah',    'note' => null],
    ],

    'fee_note' => 'Transfer dari bank ke ShopeePay / DANA +Rp 1.000',
];