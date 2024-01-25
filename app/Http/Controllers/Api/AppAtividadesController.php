<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\AtividadeAnexoController;
use App\Models\AppAtividadeModel;
use App\Http\Traits\MonitoraTrait;
use App\Models\AppAtividadeAnexoModel;
use Exception;

/**
 * Controller de atividades do app
 */
class AppAtividadesController extends NajController {
    
    use MonitoraTrait;

    private $codigoCliente;

    public function onLoad() {
        $AppAtividadeModel = new AppAtividadeModel;

        $user = $this->getUserFromToken();

        $codigoCliente = $AppAtividadeModel->getRelacionamentoClientes($user->id);
        $this->codigoCliente = $codigoCliente;

        if (!$codigoCliente) {
            $this->throwException('Usuário sem relacionamento com cliente');
        }

        $AppAtividadeModel->addRawFilter("A.CODIGO_CLIENTE IN ({$codigoCliente})");
        $AppAtividadeModel->addRawFilter("A.ENVIAR = 'S'");

        $this->setModel($AppAtividadeModel);
    }

    protected function processPaginationAfter($data) {
        $totalHoras = $this->getModel()
            ->getTotalHoras($this->codigoCliente);
        
        if (empty($totalHoras)) {
            $totalHoras = '00:00:00';
        }

        $data['total_horas'] = $totalHoras;

        return $data;
    }
    
    public function monitoracao() {
        return $this->resolveResponse(
            $this->monitora(
                'AcessoAreaClienteAPPAtividades',
                'Pesquisou por dados na rotina Atividades'
            )
        );
    }

    public function getAttachments($key) {
        $key = $this->parseQueryFilter($key);

        $AppAtividadeAnexoModel = new AppAtividadeAnexoModel;
        $AppAtividadeAnexoModel->addFixedFilter('CODIGO_ATIVIDADE', $key->CODIGO);

        return $AppAtividadeAnexoModel->makePagination();
    }

    public function getFileDownload() {
        $erro = 0;
        $message = '';

        try {
            $params = $this->validateRequestParamsExistsByArray([
                'adv_id'     => 'Id da advocacia',
                'anexo_id' => 'Id do anexo',
            ]);
        } catch (Exception $e) {
            $erro = 1;
            $message = $e->getMessage();
        }

        if ($erro) {
            return $this->resolveResponse([
                'existe'  => 0,
                'base64'  => null,
                'erro'    => 1,
                'message' => $message,
            ], 401);
        }

        $AtividadeAnexoController = new AtividadeAnexoController;

        $data = $AtividadeAnexoController->download(
            base64_encode(
                json_encode(['identificador' => $params['adv_id'], 'id' => $params['anexo_id']])
            )
        );

        if (!$data) {
            return $this->resolveResponse([
                'existe'  => 0,
                'base64'  => null,
                'erro'    => 1,
                'message' => 'O arquivo não foi encontrado no servidor para download',
            ], 404);

            return $this->resolveResponse($data, 200);
        }

        //produção
        $appDir = str_replace('naj-adv-web', 'najgestaoweb/files', env('APP_DIR'));
        $appUrl = str_replace('naj-adv-web/public/', 'najgestaoweb/files/', env('APP_URL'));

        // dd($appUrl, $appDir);

        //local
        // $appDir = 'C:/Users/mybet/Desktop/NAJ/najgestaoweb/files/';
        $extension = explode('.', request()->get('fileName'))[1];
        $nameFile = md5(uniqid()) . '-' . time() . '.' . $extension;
        $pathFile = $appDir . $nameFile;

        file_put_contents($pathFile, $data); // Gravando o arquivo

        $link = $appUrl . $nameFile; //Link publico

        $existe = 1;

        $response = [
            'existe'  => $existe,
            'link'  => $link,
            'erro'    => $erro,
            'message' => $message,
        ];

        return $this->resolveResponse($response, $response['erro'] == 1 ? 404 : 200);
    }

    private function validateRequestParamsExistsByArray($params) {
        // validando os parâmetros
        foreach ($params as $paramName => $paramAlias) {
            $value = request()->get($paramName);

            if (!$value && $value != 0 || is_null($value)) {
                $this->throwException("Parâmetro {$paramAlias} não definido");
            }

            $params[$paramName] = $value;
        }

        return $params;
    }

}
