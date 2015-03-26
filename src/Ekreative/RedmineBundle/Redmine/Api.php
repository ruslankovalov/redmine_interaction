<?php
/**
 * Created by PhpStorm.
 * User: madman
 * Date: 22.03.15
 * Time: 21:29
 */

namespace Ekreative\RedmineBundle\Redmine;

class Api {

    private $url;
    private $apiKey;


    public function __construct()
    {
        $this->setUrl('https://redmine.ekreative.com')
             ->setApiKey('2fda745bb4cdd835fdf41ec1fab82a13ddc1a54c');
        return $this;
    }

    public function getProjectList()
    {
        $path = '/projects.json';
        $result = $this->request($path);
        return $result;
    }

    public function getIssuesPerProject($projectId, $page, $limit)
    {
        $offset = $limit * ($page - 1);
        $path = "/issues.json?project_id=$projectId&offset=$offset&limit=$limit";
        $result = $this->request($path);
        return $result;
    }

    public function request($path, $method = 'GET', $data = '')
    {

        $curl = curl_init($this->getUrl() . $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-Redmine-API-Key: ' . $this->getApiKey()
        ));
        switch ($method) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }
}