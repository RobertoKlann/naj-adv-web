<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;

/**
 * Controller base de notificações pusher do sistema.
 * 
 * @package    Controllers
 * @subpackage Api
 * @author     Roberto Oswaldo Klann
 * @since      10/04/2021
 */
class NotificacaoPushController extends NajController {
    
    /**
     * @var object GuzzleHttp;
     */
    protected $Client;

    public function __construct() {
        $this->Client  = new \GuzzleHttp\Client();
    }

    /**
     * @return array
     */
    protected function getHeaders() {
        return [
            "Authorization" => $this->tokenAccess,
            "Accept"        => "application/json; charset=UTF-8;",
            "Content-Type"  => "application/json; charset=UTF-8;"
        ];
    }

    /**
     * Envia a notificação.
     */
    protected function sendNotification($parametersBody) {
        return $this->Client->post(
            $this->urlBase,
            [
                'headers' => $this->getHeaders(),
                'json'    => $parametersBody
            ]
        );
    }

}