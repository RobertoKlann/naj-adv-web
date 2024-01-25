<?php

namespace App\Models;

use App\Models\NajModel;

/**
 * Modelo de Pessoa x Usuário.
 *
 * @package    Controllers
 * @subpackage NajWeb
 * @author     Roberto Oswaldo Klann
 * @since      23/01/2020
 */
class PessoaUsuarioModel extends NajModel {

    protected function loadTable() {
        $this->setTable('pessoa_usuario');
        
        $this->addColumn('codigo_pessoa', true);
        $this->addColumn('perfil');
        $this->addColumn('externo');
        $this->addColumn('situacao');
        $this->addColumn('senha');
        $this->addColumn('email_origem');
        $this->addColumn('supervisor_agenda');

    }

}