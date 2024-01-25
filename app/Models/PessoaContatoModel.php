<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Model Pessoa Contato.
 *
 * @package    Models
 * @subpackage NajWeb
 * @author     William Goebel
 * @since      08/11/2020
 */
class PessoaContatoModel extends NajModel {

    /**
     * Carrega Tabela
     */
    protected function loadTable() {
        $this->setTable('pessoa_contato');

        $this->addColumn('CODIGO', true);
        $this->addColumn('CODIGO_DIVISAO');
        $this->addColumn('CODIGO_GRUPO');
        $this->addColumn('CODIGO_PESSOA')->addJoin('pessoa', 'CODIGO');;
        $this->addColumn('CONTATO');
        $this->addColumn('TIPO');
        $this->addColumn('PESSOA');
        $this->addColumn('PRINCIPAL');
        $this->addColumn('NOTIFICA');
        $this->addColumn('AGENDA');
        $this->addColumn('TEXTOS');
        
        $this->addColumnFrom('pessoa', 'NOME', 'NOME');
        
        $this->primaryKey = 'CODIGO';
    }
    
    /**
     * Função
     * 
     * @return string
     */
    public function funcao(){
        return;
    }
}
