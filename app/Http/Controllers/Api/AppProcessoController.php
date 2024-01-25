<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\NajController;
use App\Http\Controllers\NajWeb\GoogleCloudStorageController;
use App\Http\Controllers\NajWeb\ProcessoAnexoController;
use App\Models\AppProcessoModel;
use App\Models\AppProcessoMovimentacaoModel;
use App\Models\AppProcessoAtividadesModel;
use App\Http\Traits\MonitoraTrait;
use App\Models\AppProcessoAnexoModel;
use Illuminate\Support\Facades\Storage;

/**
 * Controller de processos (aplicativo)
 *
 * @since 2020-04-14
 */
class AppProcessoController extends NajController {
    
    use MonitoraTrait;

    const OP_ATIVOS = '=';
    const OP_ENCERRADOS = '!=';

    public function onLoad() {
        $currentUrl = URL::current();

        $AppProcessoModel = new AppProcessoModel;

        if (strpos($currentUrl, '/ativos')) {
            $AppProcessoModel->addRawFilter(
                $this->getStatusFilter(self::OP_ATIVOS)
            );
        } else if (strpos($currentUrl, '/encerrados')) {
            $AppProcessoModel->addRawFilter(
                $this->getStatusFilter(self::OP_ENCERRADOS)
            );
        }

        $user = $this->getUserFromToken();
        $codigoCliente = $AppProcessoModel->getRelacionamentoClientes($user->id);

        if (!$codigoCliente) {
            $this->throwException('Usuário sem relacionamento com cliente');
        }

        $AppProcessoModel->addRawFilter("(PRC.CODIGO_CLIENTE IN ({$codigoCliente})
            OR PRC.CODIGO IN (
                SELECT CODIGO_PROCESSO
                  FROM PRC_GRUPO_CLIENTE
                 WHERE CODIGO_CLIENTE IN ({$codigoCliente})
        ))");

        $this->setModel($AppProcessoModel);
    }

    private function getStatusFilter($operator) {
        return "(
            SELECT ATIVO
              FROM PRC_SITUACAO
             WHERE CODIGO = PRC.CODIGO_SITUACAO
        ) {$operator} 'S'";
    }

    public function getPartes($key) {
        return $this->resolveResponse(
            $this->getModel()->getPartes($key)
        );
    }

    public function getMovimentacao($key) {
        $key = $this->parseQueryFilter($key);

        $AppProcessoMovimentacaoModel = new AppProcessoMovimentacaoModel;
        $AppProcessoMovimentacaoModel->addFixedFilter('CODIGO_PROCESSO', $key->CODIGO);

        return $AppProcessoMovimentacaoModel->makePagination();
    }

    public function getAtividades($key) {
        $key = $this->parseQueryFilter($key);

        $AppProcessoAtividadesModel = new AppProcessoAtividadesModel;
        $AppProcessoAtividadesModel->addFixedFilter('CODIGO_PROCESSO', $key->CODIGO);

        return $AppProcessoAtividadesModel->makePagination();
    }

    public function getAttachments($key) {
        $key = $this->parseQueryFilter($key);

        $AppProcessoAnexoModel = new AppProcessoAnexoModel;
        $AppProcessoAnexoModel->addFixedFilter('CODIGO_PROCESSO', $key->CODIGO);
        $AppProcessoAnexoModel->addFixedFilter('DESCRICAO', 'DIR', false, 'D');
        $AppProcessoAnexoModel->addRawFilter('SERVICOS_CLIENTE = "S"');

        return $AppProcessoAnexoModel->makePagination();
    }
    
    public function monitoracao() {
        return $this->resolveResponse(
            $this->monitora(
                'AcessoAreaClienteAPPProcessos',
                'Pesquisou por dados na rotina Processos'
            )
        );
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

        $ProcessoAnexoController = new ProcessoAnexoController;

        $data = $ProcessoAnexoController->download(
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
