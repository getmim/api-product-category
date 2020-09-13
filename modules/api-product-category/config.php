<?php

return [
    '__name' => 'api-product-category',
    '__version' => '0.0.1',
    '__git' => 'git@github.com:getmim/api-product-category.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'https://iqbalfn.com/'
    ],
    '__files' => [
        'modules/api-product-category' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'product-category' => NULL
            ],
            [
                'product' => NULL
            ],
            [
                'api' => NULL
            ],
            [
                'lib-app' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'ApiProductCategory\\Controller' => [
                'type' => 'file',
                'base' => 'modules/api-product-category/controller'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'api' => [
            'apiProductCategory' => [
                'path' => [
                    'value' => '/product/category'
                ],
                'handler' => 'ApiProductCategory\\Controller\\Category::index',
                'method' => 'GET'
            ],
            'apiProductCategorySingle' => [
                'path' => [
                    'value' => '/product/category/(:identity)',
                    'params' => [
                        'identity' => 'slug'
                    ]
                ],
                'handler' => 'ApiProductCategory\\Controller\\Category::single',
                'method' => 'GET'
            ],
            'apiProductCategoryProduct' => [
                'path' => [
                    'value' => '/product/category/(:identity)/product',
                    'params' => [
                        'identity' => 'slug'
                    ]
                ],
                'handler' => 'ApiProductCategory\\Controller\\Category::product',
                'method' => 'GET'
            ]
        ]
    ]
];