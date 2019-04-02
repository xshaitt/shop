<?php

return [
    [
        'key' => 'sales',
        'name' => 'admin::app.admin.system.sales',
        'sort' => 1
    ], [
        'key' => 'sales.paymentmethods',
        'name' => 'admin::app.admin.system.payment-methods',
        'sort' => 2,
    ], [
        'key' => 'sales.paymentmethods.cashondelivery',
        'name' => 'admin::app.admin.system.cash-on-delivery',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'title',
                'title' => 'admin::app.admin.system.title',
                'type' => 'text',
                'validation' => 'required',
                'channel_based' => false,
                'locale_based' => true
            ], [
                'name' => 'description',
                'title' => 'admin::app.admin.system.description',
                'type' => 'textarea',
                'channel_based' => false,
                'locale_based' => true
            ], [
                'name' => 'active',
                'title' => 'admin::app.admin.system.status',
                'type' => 'select',
                'options' => [
                    [
                        'title' => 'Active',
                        'value' => true
                    ], [
                        'title' => 'Inactive',
                        'value' => false
                    ]
                ],
                'validation' => 'required'
            ]
        ]
    ]
];