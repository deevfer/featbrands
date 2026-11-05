<?php

return [
    'public_url' => env('SEO_PUBLIC_URL', 'https://www.featbrands.com'),
    'indexing_enabled' => env('SEO_INDEXING_ENABLED', true),
    'site_id' => 'featbrands-laravel-v1',
    'sources' => [
        'OpenAI' => 'https://developers.openai.com/api/docs/bots',
        'Perplexity' => 'https://docs.perplexity.ai/docs/resources/perplexity-crawlers',
        'Google' => 'https://developers.google.com/search/docs/fundamentals/ai-optimization-guide',
        'HostGator' => 'https://www.hostgator.com/help/article/php-configuration-plugin',
    ],
];
