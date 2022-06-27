<?php

namespace App\Http\Controllers;

use App\Models\BazzReach;
use Illuminate\Http\Request;
use App\Http\api\TwitterApi;
use Illuminate\Support\Str;

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
        return view('bazzreachs.index', compact('bazzReachs'));    }

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
     * @param  \App\Http\Requests\UpdateBazzReachRequest  $request
     * @param  \App\Models\BazzReach  $bazzReach
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBazzReachRequest $request, BazzReach $bazzReach)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BazzReach  $bazzReach
     * @return \Illuminate\Http\Response
     */
    public function destroy(BazzReach $bazzReach)
    {
        //
    }

    public function search(Request $request)
    {
        $searchWord = $request->input('search_word');
        $twitterApi = new TwitterApi();
        $totalTweets = $twitterApi->serachTweets($searchWord);
        $tweetResult = Str::limit($totalTweets, 300);

        $bazzReachs = BazzReach::orderBy('created_at', 'desc')->get();
        return view('bazzreachs.index', compact('searchWord','tweetResult','totalTweets','bazzReachs'));
    }

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
        return view('bazzreachs.index', compact('bazzReachs'));
    }
}
