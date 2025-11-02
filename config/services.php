<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'rapidapi' => [
        'key' => env('RAPIDAPI_KEY'),
        'host' => env('RAPIDAPI_HOST', 'real-time-amazon-data.p.rapidapi.com'),
        'base_url' => env('AMAZON_API_BASE_URL', 'https://real-time-amazon-data.p.rapidapi.com'),
    ],

    'node_api' => [
        'base_url' => env('NODE_API_BASE_URL', 'http://localhost:4000/api'),
    ],

    // YouTube API Configuration
    'youtube' => [
        'api_key' => env('YOUTUBE_API_KEY'),
        'rapidapi_key' => env('RAPIDAPI_KEY'),
        'rapidapi_host' => env('YOUTUBE_RAPIDAPI_HOST', 'youtube-v31.p.rapidapi.com'),
        'base_url' => env('YOUTUBE_API_BASE_URL', 'https://youtube-v31.p.rapidapi.com'),
        'max_results' => env('YOUTUBE_MAX_RESULTS', 5),
    ],

    // Google Shopping API Configuration
    'google_shopping' => [
        'rapidapi_key' => env('RAPIDAPI_KEY'),
        'rapidapi_host' => env('GOOGLE_SHOPPING_HOST', 'real-time-product-search.p.rapidapi.com'),
        'base_url' => env('GOOGLE_SHOPPING_BASE_URL', 'https://real-time-product-search.p.rapidapi.com'),
    ],

    // Imgur API Configuration
    'imgur' => [
        'client_id' => env('IMGUR_CLIENT_ID'),
        'client_secret' => env('IMGUR_CLIENT_SECRET'),
        'rapidapi_key' => env('RAPIDAPI_KEY'),
        'rapidapi_host' => env('IMGUR_RAPIDAPI_HOST', 'imgur-apiv3.p.rapidapi.com'),
        'base_url' => env('IMGUR_API_BASE_URL', 'https://api.imgur.com/3'),
    ],

    // Currency Exchange API Configuration
    'currency_exchange' => [
        'rapidapi_key' => env('RAPIDAPI_KEY'),
        'rapidapi_host' => env('CURRENCY_RAPIDAPI_HOST', 'currency-conversion-and-exchange-rates.p.rapidapi.com'),
        'base_url' => env('CURRENCY_API_BASE_URL', 'https://currency-conversion-and-exchange-rates.p.rapidapi.com'),
        'default_currency' => env('DEFAULT_CURRENCY', 'USD'),
    ],

    // ChatGPT / OpenAI API Configuration
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'rapidapi_key' => env('RAPIDAPI_KEY'),
        'rapidapi_host' => env('OPENAI_RAPIDAPI_HOST', 'cheapest-gpt-4-turbo-gpt-4-vision-chatgpt-openai-ai-api.p.rapidapi.com'),
        'base_url' => env('OPENAI_API_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 500),
    ],

];
