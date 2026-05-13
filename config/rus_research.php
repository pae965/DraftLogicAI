<?php

/**
 * RUS Research CMS Configuration
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Institution Defaults
    |--------------------------------------------------------------------------
    | ค่า default ที่ใช้เมื่อ user สร้างบทความ
    */
    'institution' => [
        'name_th'  => env('RUS_INSTITUTION_TH', 'มหาวิทยาลัยเทคโนโลยีราชมงคลสุวรรณภูมิ'),
        'name_en'  => env('RUS_INSTITUTION_EN', 'Rajamangala University of Technology Suvarnabhumi'),
        'short'    => env('RUS_INSTITUTION_SHORT', 'RUS'),
    ],

    'faculty' => [
        'name_th_default' => env('RUS_FACULTY_TH_DEFAULT', 'คณะนิติศาสตร์'),
        'name_en_default' => env('RUS_FACULTY_EN_DEFAULT', 'Faculty of Law'),
    ],

    'degree' => [
        'name_th_default' => 'นิติศาสตรมหาบัณฑิต',
        'name_en_default' => 'Master of Laws',
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    */
    'export' => [
        'max_pages'    => env('EXPORT_MAX_PAGES', 25),
        'pdf_timeout'  => env('EXPORT_PDF_TIMEOUT', 60),
        'font_path'    => env('EXPORT_FONT_PATH', 'public/fonts/th-sarabun-new'),
        'font_family'  => 'TH Sarabun New',
    ],

    /*
    |--------------------------------------------------------------------------
    | Page Layout (per RUS spec)
    |--------------------------------------------------------------------------
    */
    'layout' => [
        'paper_size'  => 'A4',
        'orientation' => 'portrait',
        'margin_in'   => 1.0,  // inches all sides
        'font_sizes'  => [
            'title'             => 18,
            'author'            => 16,
            'section_heading'   => 16,
            'body'              => 16,
            'footnote'          => 14,
            'bibliography'      => 16,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'max_title_length'    => 500,
        'max_author_count'    => 10,
        'max_keywords'        => 8,
        'min_keywords'        => 3,
        'abstract_max_words'  => 350,
    ],
];
