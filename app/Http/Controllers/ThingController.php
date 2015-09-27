<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use XPathSelector\Exception\NodeNotFoundException;
use XPathSelector\Selector;

class ThingController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param $link
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function show($link)
    {
        $client = new Client();

        $baseUrl = 'https://www.youmagine.com/';
        $response = $client->get($baseUrl . 'designs/' . $link);
        $code = $response->getBody();
        $xs = Selector::loadHTML($code);

        // Scrape Youmagine thing information from DOM
        try {
            $name = $xs->find('/html/body/div[2]/div/h1')->extract();
            $description = $xs->find('//*[@id="information"]/div[2]')->extract();
            $files = $xs->findAll('//*[@id="documents"]/div/ul/li[*]/div[3]/div[1]/a')->map(function ($node) {
                return $node->find('@href')->extract();
            });
        } catch (NodeNotFoundException $e) {
            return response()->view('thing.none');
        }

        // Get files
        $downloadLinks = [];
        foreach ($files as $file) {
            $response = $client->get($baseUrl . $file, ['allow_redirects' => false]);
            $code = $response->getBody();
            preg_match('/"(.*?)"/', $code, $downloadLinkMatch);
            $downloadLinks[] = $downloadLinkMatch[1];
        }

        // Get access token
        $response = $client->request('POST', 'https://developer.api.autodesk.com/authentication/v1/authenticate', [
            'form_params' => [
                'client_id' => env('AUTODESK_CLIENT_ID', ''),
                'client_secret' => env('AUTODESK_CLIENT_SECRET', ''),
                'grant_type' => 'client_credentials',
            ],
        ]);
        $authToken = json_decode($response->getBody())->access_token;

        // Create a bucket
        $bucketKey = Str::lower(Str::random(16));
        $response = $client->request('POST', 'https://developer.api.autodesk.com/oss/v2/buckets', [
            'json' => [
                'bucketKey' => $bucketKey,
                'policyKey' => 'transient',
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $authToken,
            ]
        ]);
        $bucketKey = json_decode($response->getBody())->bucketKey;

        // Upload to bucket
        $bucket = [];
        foreach ($downloadLinks as $downloadLink) {
            $fileName = pathinfo($downloadLink)['basename'];

            $file = fopen(base_path('public/cache/' . $fileName), 'w');
            /** @noinspection PhpUnusedLocalVariableInspection */
            $response = $client->get($downloadLink, ['sink' => $file]);
            $file = fopen(base_path('public/cache/' . $fileName), 'r');

            $response = $client->request('PUT',
                'https://developer.api.autodesk.com/oss/v2/buckets/' . $bucketKey . '/objects/' . $fileName,
                [
                    'body' => $file,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $authToken,
                    ]
                ]);

            $body = json_decode($response->getBody());
            $bucket[] = [
                'filename' => $body->objectKey,
                'urn' => $body->objectId,
            ];
        }

        // Set up references
        $references = [
            'master' => $bucket[0]['urn'],
            'dependencies' => []
        ];

        foreach ($bucket as $file) {
            if ($file['filename'] === $bucket[0]['filename']) {
                continue;
            }

            $references['dependencies'][] = [
                'file' => $file['urn'],
                'metadata' => [
                    'childPath' => $file['filename'],
                    'parentPath' => $bucket[0]['filename'],
                ]
            ];
        }

        $response = $client->post('https://developer.api.autodesk.com/references/v1/setreference', [
            'json' => $references,
            'headers' => [
                'Authorization' => 'Bearer ' . $authToken,
            ]
        ]);

        // Register data with the viewing services

        $response = $client->post('https://developer.api.autodesk.com/viewingservice/v1/register', [
            'json' => [
                'urn' => base64_encode($bucket[0]['urn']),
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $authToken,
            ]
        ]);

        $urn = base64_encode($bucket[0]['urn']);


        return response()->view('thing.show', compact('name', 'description', 'urn', 'authToken'));
    }
}
