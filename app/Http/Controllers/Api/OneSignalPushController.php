<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\NotificacaoPushController;
use App\Http\Controllers\NajWeb\EmpresaController;

/**
 * Controller de notificações pusher para o One Signal do sistema.
 * 
 * @package    Controllers
 * @subpackage Api
 * @author     Roberto Oswaldo Klann
 * @since      10/04/2021
 */
class OneSignalPushController extends NotificacaoPushController {
    
    protected $tokenAccess = 'Bearer ZTYyNjJjNjEtZGEwNi00OTY0LWJiMjgtN2MzMTQ2MWUzYzI';    
    protected $urlBase = 'https://onesignal.com/api/v1/notifications';
    protected $appId = 'ebbc160a-a51d-4c13-b7bf-cff2dcfd3fa0';

    public function newPushNotification($data) {
        $company = (object) $this->getDataSendNotificationCompany();

        $utf8 = [
            '/[áàâãªä]/u'   =>   'a',
            '/[ÁÀÂÃÄ]/u'    =>   'A',
            '/[ÍÌÎÏ]/u'     =>   'I',
            '/[íìîï]/u'     =>   'i',
            '/[éèêë]/u'     =>   'e',
            '/[ÉÈÊË]/u'     =>   'E',
            '/[óòôõºö]/u'   =>   'o',
            '/[ÓÒÔÕÖ]/u'    =>   'O',
            '/[úùûü]/u'     =>   'u',
            '/[ÚÙÛÜ]/u'     =>   'U',
            '/ç/'           =>   'c',
            '/Ç/'           =>   'C',
            '/ñ/'           =>   'n',
            '/Ñ/'           =>   'N',
            '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
            '/[“”«»„]/u'    =>   ' ', // Double quote
            '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
        ];

        // $message = "ãõé çair açái rogério R$444 lambidas ação, vencimento RUBSHCIDG";

        $message = preg_replace(array_keys($utf8), array_values($utf8), $data['message']);

        // dd($message, $data);

        $parametersBody = [
            'app_id' => $this->appId,
            'language' => 'en',
            'headings' => [
                'en' => $company->name->original,
            ],
            'contents' => [
                'en' => $message,
            ],
            'data' => [
                'action' => $data['action'],
                'message' => [
                    'id_cliente' => $company->id->original,
                    'id_usuario_receber' => $data['usuario_id']
                ]
            ],
            'small_icon' => 'ic_stat_onesignal_default',
            'include_player_ids' => [$data['pusher_id']]
        ];

        return $this->sendNotification($parametersBody);
    }

    private function getDataSendNotificationCompany() {
        $EmpresaController = new EmpresaController();
        $codigoEmpresa     = $EmpresaController->getIdentificadorEmpresa();
        $nomeEmpresa       = $EmpresaController->getNomeFirstEmpresa();

        return ['id' => $codigoEmpresa, 'name' => $nomeEmpresa];
    }

}