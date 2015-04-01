<?php
/**
 * Created by PhpStorm.
 * User: madman
 * Date: 22.03.15
 * Time: 21:29
 */

namespace Ekreative\RedmineBundle\Redmine;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Api {

    private $url;
    private $apiKey;

    /**
     * Sets Url and Api key for authorization
     */
    public function __construct()
    {
        $this->setUrl('https://redmine.ekreative.com')
             ->setApiKey('2fda745bb4cdd835fdf41ec1fab82a13ddc1a54c');
        return $this;
    }

    /**
     * Retrieve list of all projects
     *
     * @return object
     */
    public function getProjectList()
    {
        $path = '/projects.json';
        $result = $this->request($path);
        return $result;
    }

    /**
     * Get project by ID
     *
     * @param int $projectId
     * @return object
     */
    public function getProject($projectId)
    {
        $path = "/projects/$projectId.json";
        $result = $this->request($path);
        return $result;
    }

    /**
     * Retrieve list of all issues of given project
     *
     * @param int $projectId
     * @param int $page
     * @param int $limit
     * @return object
     */
    public function getIssues($projectId, $page, $limit)
    {
        $offset = $limit * ($page - 1);
        $path = "/issues.json?project_id=$projectId&offset=$offset&limit=$limit";
        $result = $this->request($path);
        return $result;
    }

    /**
     * Log time for project or issue(if issue id is given)
     *
     * @param array $data
     * @return object
     */
    public function LogTime($data)
    {
        if ($data['issue_id']) {
            unset($data['project_id']);
        }
        $json = json_encode(array('time_entry' => $data));
        $result = $this->request('/time_entries.json', 'POST', $json);
        return $result;
    }

    /**
     * Perform request to API and returns decoded json
     *
     * @param $path
     * @param string $method
     * @param string $data
     * @return mixed
     * @throws FatalErrorException
     */
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
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (!in_array($responseCode, array(200, 201))) {
            switch ($responseCode) {
                case 404:
                    throw new NotFoundHttpException($result);
                    break;
                default:
                    throw new \Exception($result);
                    break;
            }
        }
        curl_close($curl);
        return json_decode($result);
    }

    /**
     * Get URL of API
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     *  Get API key for authorization
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set URL of API
     *
     * @param string $url
     * @return Api
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set API key for authorization
     *
     * @param string $apiKey
     * @return Api
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }
}