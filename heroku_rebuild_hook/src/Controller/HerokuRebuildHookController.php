<?php

namespace Drupal\heroku_rebuild_hook\Controller;

use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class HerokuRebuildHookController{
    private $config, $client;

    public function __construct(){
       $this->config = \Drupal::config('heroku_rebuild_hook.settings');
       $this->client = \Drupal::httpClient();
    }

    private function getLastCommitHash(){
        $masterURL = $this->config->get('github_master_url');
        $gitKey = $this->config->get('github_API_key');
        try{
            $response = $this->client->get($masterURL, ['headers' => ['Authorization' => 'token '. $gitKey]]);
        }
        catch(Exception $e){
            \Drupal::logger("Heroku Rebuild URL")->error("Error, please check that the URLs are correct");
            return -1;
        }
        $response = json_decode($response->getBody());
        return $response->sha;

    }
    // O-auth to heroku platfotm api to re-build
    private function callPlatformAPI($commit){
        $gitTarball = $this->config->get('github_tarball_url');
        $gitKey = $this->config->get('github_API_key');

        if( $gitKey != 'none'){
            $gitTarball = explode("https://", $gitTarball);
            $gitTarball = "https://" . $gitKey . "@" . $gitTarball[1]; //url to access a private repo
        }

        $data = [
            "source_blob" => ["url" => $gitTarball, "version" => $commit, "version_description" => "Rebuilding app to update data" ],
        ];

        $url = $this->config->get('heroku_build_url');
        try{
            $response = $this->client->post($url, 
                [
                    'headers' => [
                        'Content-Type' => 'application/json', 
                        'Accept' => 'application/vnd.heroku+json; version=3',
                        'Authorization' => 'Bearer ' . $this->config->get('heroku_API_key')
                    ], 
                    'body' => json_encode($data)
                ]
            );
        }
        catch(Exception $e){
            \Drupal::logger("Heroku Rebuild URL")->error("Error. Check that the config URLs are correct");
            return false;
        }
        return true;
    }

    public function buildApp(Request $request){
        $commit = $this->getLastCommitHash();
        if($this->callPlatformAPI($commit)){
            \Drupal::logger("Heroku Rebuild URL")->notice("Rebuilding App. It may may take a few minutes for the site to update changes.");
        }
        else \Drupal::logger("Heroku Rebuild URL")->error("Error Rebuilding App. Make sure all URLS are correct or rebuild from Heroku manually");
        return new Response(json_encode(['status' => "Rebuilding App"]), Response::HTTP_OK, ['content-type' => 'application/json']);;

    }
}