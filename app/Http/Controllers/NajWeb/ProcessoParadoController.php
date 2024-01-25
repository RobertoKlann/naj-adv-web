<?php

namespace App\Http\Controllers\NajWeb;

use App\Http\Controllers\NajController;
use App\Models\ProcessosParadoModel;

/**
 * Controller de processos.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Klann
 * @since      18/04/2021
 */
class ProcessoParadoController extends NajController {

    public function onLoad() {
        $this->setModel(new ProcessosParadoModel);
    }

    /**
     * Create da rota de Processo
     * @return view
     */
    public function index() {
        return view('najWeb.consulta.ProcessosParadoConsultaView')->with('is_process_parado', true);
    }

    public function paginate() {
        //verificando se precisa registrar o monitoramento
        if($this->getMonitoramentoController() && !$this->isSkipsRoute()) {
            $this->getMonitoramentoController()->storeMonitoramento(self::PAGINATE_ACTION);
        }

        return $this->processPaginationAfter(
            $this->getModel()->makePagination()
        );
    }

}