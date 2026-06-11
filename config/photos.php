<?php

return [

    'disk' => env('PHOTO_STORAGE_DISK', 'public'),

    'thumbnail_max_width' => (int) env('PHOTO_THUMBNAIL_MAX_WIDTH', 1200),

    'thumbnail_quality' => (int) env('PHOTO_THUMBNAIL_QUALITY', 82),

    'display_max_width' => (int) env('PHOTO_DISPLAY_MAX_WIDTH', 2400),

    'watermark_text' => env('PHOTO_WATERMARK_TEXT'),

    'watermark_font' => env('PHOTO_WATERMARK_FONT'),

    'watermark_lowercase' => env('PHOTO_WATERMARK_LOWERCASE', true),

];
