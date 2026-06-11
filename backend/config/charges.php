<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Commission and Charge Slabs
    |--------------------------------------------------------------------------
    |
    | Define your commission slabs here. Each key corresponds to the
    | `charge_slab_key` in the Service meta column.
    |
    | type: 'flat' (absolute amount) or 'percent' (% of transaction amount)
    | value: positive for commission (credit), negative for charge (debit)
    |
    */

    'aeps_cw_default' => [
        [
            'min_amount' => 100,
            'max_amount' => 1000,
            'type'       => 'flat',
            'value'      => 5.00, // Rs 5 flat commission
        ],
        [
            'min_amount' => 1001,
            'max_amount' => 3000,
            'type'       => 'percent',
            'value'      => 0.2,  // 0.2% commission
        ],
        [
            'min_amount' => 3001,
            'max_amount' => 10000,
            'type'       => 'flat',
            'value'      => 10.00, // Rs 10 flat commission
        ],
    ],

];
