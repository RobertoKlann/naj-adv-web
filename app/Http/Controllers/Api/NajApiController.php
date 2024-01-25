<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Controllador base da Api.
 * 
 * @package    Controllers
 * @subpackage Api
 * @author     Roberto Oswaldo Klann
 * @since      16/01/2020
 */
class NajApiController {

    /**
     * @var object GuzzleHttp;
     */
    protected $Client;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $jsonRequest;

    /**
     * @var string
     */
    protected $baseUrl;


    public function __construct() {
        $this->Client  = new \GuzzleHttp\Client();
    }

    protected function get($query = null) {
        $url     = $this->baseUrl.$this->url;
        $headers = $this->getHeaders();
        $query   = ($query) ? $query : null;

        return $this->Client->request(
            'GET',
            $url,
            [
                'headers' => $headers,
                'query' => $query
            ]
        );
    }

    protected function post($parameters) {
        return $this->Client->post(
            sprintf(
                '%s%s',
                $this->getUrlBase(),
                $this->getUrl()
            ),
            [
                'headers' => $this->getHeaders(),
                'json'    => $parameters
            ]
        );
    }

    protected function put($parameters) {
        $url     = $this->baseUrl.$this->url;
        $headers = $this->getHeaders();
        return $this->Client->request(
            'PUT',
            $url,
            [
                'headers' => $headers,
                'json'    => $parameters
            ]
        );
    }

    protected function delete() {

    }

    /**
     * @return array
     */
    protected function getHeaders() {
        return [
            "Authorization" => "Bearer " . $this->getToken(),
            "Accept"        => "application/json",
            "Content-Type"  => "application/json"
        ];
    }

    protected function setUrlBase($urlBase) {
        $this->baseUrl = $urlBase;
    }

    protected function getUrlBase() {
        return $this->baseUrl;
    }

    protected function setUrl($url) {
        $this->url = $url;
    }

    protected function getUrl() {
        return $this->url;
    }

    protected function setJsonRequest($jsonRequest) {
        $this->jsonRequest = $jsonRequest;
    }

    protected function getJsonRequest() {
        return $this->jsonRequest;
    }

    protected function setToken($token) {
        $this->token = $token;
    }

    protected function getToken() {
        return $this->token;
    }

    /**
     * Obtêm registro da tabela sys_config.
     * 
     * @param string $secao valor da 'SECAO'
     * @param string $chave valor da 'CHAVE'
     * 
     * @return registro
     * 
     * @throws Exception
     */
    protected function getRecordSysConfig($secao, $chave) {
        $valor = DB::table('sys_config')
                ->select('sys_config.VALsOR')
                ->where('sys_config.SECAO', $secao)
                ->where('sys_config.CHAVE', $chave)
                ->first();
        
        if($valor) {
            return $valor->VALOR;
        }

        throw new Exception("O parâmetro Seção: {$secao} Chave: {$chave} não foi definido, favor contatar o suporte!");
        
    }

}