<?php

namespace App;

use App\Exceptions\NajException;
  
class FiltraEnvolvidos {

    public $envolvidos;

    private function generateKey($envolvido) {
        return base64_encode(implode('', [
            $envolvido->id,
            $envolvido->nome,
            $envolvido->pivot_tipo,
            $envolvido->pivot_extra_nome,
        ]));
    }

    private function applyFilter($chaves) {
        $arrayFinal = [];

        foreach ($this->envolvidos as $envolvido) {
            $chave = $this->generateKey($envolvido);

            if (in_array($chave, $chaves)) {
                $arrayFinal[] = $envolvido;

                $chaves = array_diff($chaves, [$chave]);
            }
        }

        return $arrayFinal;
    }

    public function removeDuplicates() {
        $chaves = array_map(array($this, 'generateKey'), $this->envolvidos);

        $chavesFiltradas = array_unique($chaves);

        return $this->applyFilter($chavesFiltradas);
    }

}