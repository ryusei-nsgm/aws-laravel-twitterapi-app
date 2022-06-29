<?php

namespace App\Http\Api;

use Illuminate\Http\Request;
use Aws\Comprehend\ComprehendClient;

class ComprehendApi
{
    
    private $client;
    
    public function __construct()
    {
        $this->client  = new ComprehendClient([
            'credentials' => [
                'key' =>  env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY')
            ],
            'region' => 'ap-northeast-1',
            'version' => '2017-11-27'
        ]);
    }
    
    // ツイート感情分析
    public function tweetSentiment(String $total_tweets)
    {
        $total_tweets = mb_strcut($total_tweets, 0 , 5000, "UTF-8");
        $result = $this->client->detectSentiment([
          'LanguageCode' => 'ja',
          'Text' => $total_tweets
        ]);
        return $result;
    }

    // キーフレーズ分析
    public function tweetKeyPhrases(String $total_tweets)
    {
        $total_tweets = mb_strcut($total_tweets, 0 , 5000, "UTF-8");
        $result = $this->client->detectKeyPhrases([
          'LanguageCode' => 'ja',
          'Text' => $total_tweets
        ]);
        return $result;
    }

}