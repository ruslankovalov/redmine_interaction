<?php
/**
 * Created by PhpStorm.
 * User: madman
 * Date: 22.03.15
 * Time: 21:29
 */

namespace Ekreative\RedmineBundle\Redmine;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\FatalErrorException;

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

    public function getProject($projectId)
    {
        $path = "/projects/$projectId.json";
        $result = $this->request($path);
        return $result;
    }

    public function getIssues($projectId, $page, $limit)
    {
        $offset = $limit * ($page - 1);
        $path = "/issues.json?project_id=$projectId&offset=$offset&limit=$limit";
        $result = $this->request($path);
        return $result;
    }

    public function LogTime($data)
    {
        if ($data['issue_id']) {
            unset($data['project_id']);
        }
        $json = json_encode(array('time_entry' => $data));
        $result = $this->request('/time_entries.json', 'POST', $json);
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
        $result = json_decode(curl_exec($curl));
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (!in_array($responseCode, array(200, 201)) && is_null($result)) {
            switch ($responseCode) {
                case 404:
                    throw new NotFoundHttpException("Page not found");
                    break;
                case 500:
                    throw new FatalErrorException('Internal server error');
                    break;
            }
        }
        curl_close($curl);
        return $result;
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