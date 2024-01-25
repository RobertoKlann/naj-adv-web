<?php

namespace App\Models;

use App\Models\NajModel;

class UsuarioNpsModel extends NajModel {
    
    protected function loadTable() {
        $this->setTable('usuarios');

        $this->addColumn('id', true);
        $this->addColumn('usuario_tipo_id')->addJoin('usuarios_tipo');
        $this->addColumn('login');
        $this->addColumn('password')->setHidden();
        $this->addColumn('status');
        $this->addColumn('data_inclusao');
        $this->addColumn('data_baixa');
        $this->addColumn('email_recuperacao');
        $this->addColumn('mobile_recuperacao');
        $this->addColumn('nome');
        $this->addColumn('apelido');
        $this->addColumn('cpf');
        $this->addColumn('senha_provisoria');
        $this->addColumn('ultimo_acesso');

        $this->addAllColumns();

        $this->setRawBaseSelect("
                SELECT [COLUMNS]
                  FROM usuarios
                  JOIN usuarios_tipo
                    ON usuarios_tipo.id = usuarios.usuario_tipo_id
        ");
        
        $this->setOrder('status ASC, usuario_tipo_id ASC');
        
        $this->primaryKey = 'id';
    }

    public function addAllColumns() {
        $this->addRawColumn("usuarios_tipo.tipo")
             ->addRawColumn("(SELECT data_hora_inclusao
                                FROM pesquisa_respostas
                               WHERE TRUE
                                 AND id_usuario = usuarios.id
                            ORDER BY data_hora_inclusao DESC
                               LIMIT 1) as ultima_pesquisa")
             ->addRawColumn("password AS senha_alteracao_cadastro");
    }
    
}