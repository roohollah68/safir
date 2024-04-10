<?php
return [
    'format' => [200, 100],
    'default_font' => 'iransans',
    'margin_left' => 2,
    'margin_right' => 2,
    'margin_top' => 2,
    'margin_bottom' => 2,
    'margin_header' => 0,
    'margin_footer' => 0,
    'orientation' => 'P',
    'display_mode' => 'fullpage',
    'auto_language_detection' => false,
    'custom_font_dir' => base_path('public/css/'), // don't forget the trailing slash!
    'custom_font_data' => [
        'iransans' => [ // must be lowercase and snake_case
            'R' => 'IRANSans/ttf/IRANSansWeb(FaNum).ttf',    // regular font
            'B' => 'IRANSans/ttf/IRANSansWeb(FaNum)_Bold.ttf',       // optional: bold font
            'I' => 'IRANSans/ttf/IRANSansWeb(FaNum).ttf',     // optional: italic font
            'BI' => 'IRANSans/ttf/IRANSansWeb(FaNum)_Bold.ttf', // optional: bold-italic font
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ],
        'bnazanin' => [ // must be lowercase and snake_case
            'R' => 'BNAZANIN.ttf',    // regular font
            'B' => 'BNAZANIN.ttf',       // optional: bold font
            'I' => 'BNAZANIN.ttf',     // optional: italic font
            'BI' => 'BNAZANIN.ttf',     // optional: bold-italic font
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ],
    ]
];

return [
    'mode' => '',
    'format' => 'A4',
    'default_font_size' => '12',
    'default_font' => 'iransans',
    'margin_left' => 3,
    'margin_right' => 3,
    'margin_top' => 3,
    'margin_bottom' => 3,
    'margin_header' => 0,
    'margin_footer' => 0,
    'orientation' => 'P',
    'title' => 'Laravel mPDF',
    'subject' => '',
    'author' => '',
    'watermark' => '',
    'show_watermark' => false,
    'show_watermark_image' => false,
    'watermark_font' => 'sans-serif',
    'display_mode' => 'fullpage',
    'watermark_text_alpha' => 0.1,
    'watermark_image_path' => '',
    'watermark_image_alpha' => 0.2,
    'watermark_image_size' => 'D',
    'watermark_image_position' => 'P',
    'auto_language_detection' => false,
    'temp_dir' => storage_path('app'),
    'pdfa' => false,
    'pdfaauto' => false,
    'use_active_forms' => false,
    'custom_font_dir' => base_path('public/css/'), // don't forget the trailing slash!
    'custom_font_data' => [
        'iransans' => [ // must be lowercase and snake_case
            'R' => 'IRANSans/ttf/IRANSansWeb(FaNum).ttf',    // regular font
            'B' => 'IRANSans/ttf/IRANSansWeb(FaNum)_Bold.ttf',       // optional: bold font
            'I' => 'IRANSans/ttf/IRANSansWeb(FaNum).ttf',     // optional: italic font
            'BI' => 'IRANSans/ttf/IRANSansWeb(FaNum)_Bold.ttf', // optional: bold-italic font
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ],
        'bnazanin' => [ // must be lowercase and snake_case
            'R' => 'BNAZANIN.ttf',    // regular font
            'B' => 'BNAZANIN.ttf',       // optional: bold font
            'I' => 'BNAZANIN.ttf',     // optional: italic font
            'BI' => 'BNAZANIN.ttf',     // optional: bold-italic font
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ],
    ]
];

