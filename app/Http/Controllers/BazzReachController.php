<?php

namespace App\Http\Controllers;

use App\Models\BazzReach;
use Illuminate\Http\Request;
use App\Http\Api\TwitterApi;
use Illuminate\Support\Str;
use App\Models\KeyPhrase;
use App\Models\Sentiment;
use App\Http\Api\ComprehendApi;


class BazzReachController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bazzReachs = BazzReach::orderBy('created_at', 'desc')->get();
        return view('bazzreachs.index', compact('bazzReachs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $bazzreach = new BazzReach();
        $bazzreach->search_word = $request->input('search_word');
        $bazzreach->total_tweets = $request->input('total_tweets');
        $bazzreach->save();

        return redirect()->route('bazzreach.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BazzReach  $bazzReach
     * @return \Illuminate\Http\Response
     */
    public function show(BazzReach $bazzReach)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BazzReach  $bazzReach
     * @return \Illuminate\Http\Response
     */
    public function edit(BazzReach $bazzReach)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BazzReach  $bazzReach
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BazzReach $bazzreach)
    {
        $bazzreach->comment = $request->input('comment');
        $bazzreach->update();
        return redirect()->route('bazzreach.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BazzReach  $bazzReach
     * @return \Illuminate\Http\Response
     */
    public function destroy(BazzReach $bazzreach)
    {
        $bazzreach->delete();
        $bazzreach->sentiment()->delete();
        $bazzreach->keyPhrase()->delete();
        return redirect()->route('bazzreach.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $searchWord = $request->input('search_word');
        $twitterApi = new TwitterApi();
        $totalTweets = $twitterApi->serachTweets($searchWord);
        $tweetResult = Str::limit($totalTweets, 300);

        $bazzReachs = BazzReach::orderBy('created_at', 'desc')->get();
        return view('bazzreachs.index', compact('searchWord','tweetResult','totalTweets','bazzReachs'));
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function bazzsearch(Request $request)
    {
        $keyword = $request->input('keyword');
        $bazzReachs;

        if(empty($keyword)){
            $bazzReachs = BazzReach::orderBy('created_at', 'desc')->get();
        }else{
            $query = BazzReach::query();
            $keywords = explode(" ", $keyword);

            foreach ($keywords as $Key => $Value) {
                $query->orWhere('search_word', 'ilike', '%'.$Value.'%');
            }
            $bazzReachs = $query->orderBy('created_at', 'desc')->get();
        }
        //return view('bazzreachs.index', compact('bazzReachs'));
        $dataArray = [$bazzReachs];
        $data = response()->json($dataArray);
        return $data;
    }

    /**
     * Analysis the specified resource from storage.
     *
     * @param  \App\Models\BazzReach  $bazzReach
     * @return \Illuminate\Http\Response
     */
    public function analysis(BazzReach $bazzreach)
    {
        $comprehendApi = new ComprehendApi();
        $sentiment = $comprehendApi->tweetSentiment($bazzreach->total_tweets);
        $keyPhrase = $comprehendApi->tweetKeyPhrases($bazzreach->total_tweets);

        if($sentiment['Sentiment'] == "MIXED"){
            $bazzreach->sentiment = "混合";
        }elseif($sentiment['Sentiment'] == "NEGATIVE"){
            $bazzreach->sentiment = "ネガティブ";
        }elseif($sentiment['Sentiment'] == "POSITIVE"){
            $bazzreach->sentiment = "ポジティブ";
        }elseif($sentiment['Sentiment'] == "NEUTRAL"){
            $bazzreach->sentiment = "中性";
        }
        $bazzreach->update();

        $scores = $sentiment['SentimentScore'];
        foreach ($scores as $type => $score) {
            $sentiment = new Sentiment();
            $sentiment->bazz_reach_id = $bazzreach->id;
            if($type == "Mixed"){
                $sentiment->type = "混合";
            }elseif($type == "Negative"){
                $sentiment->type = "ネガティブ";
            }elseif($type == "Positive"){
                $sentiment->type = "ポジティブ";
            }elseif($type == "Neutral"){
                $sentiment->type = "中性";
            }
            $sentiment->score = round($score * 100, 1);
            $sentiment->save();
        }

        $scores = $keyPhrase['KeyPhrases'];
        foreach ($scores as $score) {
            $keyPhrase = new KeyPhrase();
            $keyPhrase->bazz_reach_id = $bazzreach->id;
            $keyPhrase->key_phrase = $score["Text"];
            $keyPhrase->score = round($score["Score"] * 100, 1);
            $keyPhrase->save();
        }
        return redirect()->route('bazzreach.index');
    }

    public function result(BazzReach $bazzreach)
    {
        $sentiments = $bazzreach->sentiment()->orderBy('score', 'desc')->get()->toArray();
        $keyPhrases = $bazzreach->keyPhrase()->orderBy('score', 'desc')->get()->toArray();
        $dataArray = [$sentiments,$keyPhrases];
        $data = response()->json($dataArray);
        return $data;
    }
}
