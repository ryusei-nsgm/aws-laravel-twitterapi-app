<?php

namespace App\Http\Api;

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterApi
{
    
    private $connection ;
    
    public function __construct()
    {
        $this->connection  = new TwitterOAuth(
            env('TWITTER_CLIENT_KEY'),
            env('TWITTER_CLIENT_SECRET'),
            env('TWITTER_CLIENT_ID_ACCESS_TOKEN'),
            env('TWITTER_CLIENT_ID_ACCESS_TOKEN_SECRET'));
    }
    
    // ツイート検索
    public function serachTweets(String $searchWord)
    {
        $totalTweets = "";
        $searchResults = $this->connection ->get("search/tweets", [
            'q' => $searchWord,
            'count' => 100,
         ]);
         
        $searchResults = json_decode(json_encode($searchResults->statuses), true);

        foreach ($searchResults as $searchResult) {
            $totalTweets .= $searchResult["text"];
        }

        $totalTweets = mb_strcut($totalTweets, 0 , 5000, "UTF-8");
        return $totalTweets;
    }
}