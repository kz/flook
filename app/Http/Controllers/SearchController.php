<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use XPathSelector\Exception\NodeNotFoundException;
use XPathSelector\Selector;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        // Ensure that the search query exists.
        $this->validate($request, [
            'query' => 'required|max:128'
        ]);

        $client = new Client();

        // Load Youmagine into DOM
        $response = $client->get('https://www.youmagine.com/search/designs?utf8=%E2%9C%93&search=' . $request->get('query'));
        $code = $response->getBody();
        $xs = Selector::loadHTML($code);

        // Scrape Youmagine results from DOM
        try {
            $links = $xs->findAll('//*[@id="js-results"]/div[1]/a')->map(function ($node) {
                return $node->find('@href')->extract();
            });
            $images = $xs->findAll('//*[@id="js-results"]/div[1]/a/div[*]/div[2]')->map(function ($node) {
                $styleTag = $node->find('@style')->extract();
                preg_match('/\'(.*?)\'/', $styleTag, $styleMatches);
                return $styleMatches[1];
            });
        } catch(NodeNotFoundException $e) {
            return response()->view('results.none');
        }

        return response()->view('results.show', compact('links', 'images'));
    }
}
