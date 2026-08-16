<?php

/*
 | Merge these keys into your existing config/services.php.
 | (The 'mailgun', 'postmark', etc. blocks from the default file are kept.)
 */

return [

    // ... keep Laravel's default service entries here ...

    'gemini' => [
        'key'   => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

    'scraper' => [
        'user_agent' => env('SCRAPER_USER_AGENT',
            'LiverpoolInBot/1.0 (+https://liverpoolin.com/bot)'),
    ],

    /*
     | News sources. Prefer 'rss' mode (ToS-friendly). Add publisher feeds
     | or the official LFC feed. The 'html' example targets NewsNow but note:
     | scraping NewsNow violates their Terms of Service — enable at your own
     | legal risk. Update selectors if the target markup changes.
     */
    'news_sources' => [
        [
            'name'    => 'This Is Anfield',
            'mode'    => 'rss',
            'url'     => 'https://www.thisisanfield.com/feed/',
            'enabled' => true,
            'limit'   => 20,
        ],
        [
            'name'    => 'NewsNow (HTML - disabled by default)',
            'mode'    => 'html',
            'url'     => 'https://www.newsnow.co.uk/h/Sport/Football/Premier+League/Liverpool',
            'base_url'=> 'https://www.newsnow.co.uk',
            'enabled' => false, // review ToS before enabling
            'limit'   => 20,
            'selectors' => [
                'item'    => 'article.hl',        // <-- verify against live markup
                'title'   => '.hl__inner .hl-title',
                'link'    => 'a.hll-a',
                'excerpt' => null,
                'image'   => null,
            ],
        ],
    ],

    'fixtures' => [
        'url' => env('FIXTURES_URL', 'https://www.liverpoolfc.com/fixtures/mens/2026'),
        'selectors' => [
            'fixture'     => '.fixture',       // <-- verify against live markup
            'home'        => '.fixture__team--home .fixture__team-name',
            'away'        => '.fixture__team--away .fixture__team-name',
            'datetime'    => 'time',
            'competition' => '.fixture__competition',
            'venue'       => '.fixture__venue',
        ],
    ],
];
