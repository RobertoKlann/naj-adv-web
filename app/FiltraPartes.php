<?php

namespace App;

use App\Exceptions\NajException;
  
class FiltraPartes {

    public $partes;

    private function generateKey($parte) {
        return base64_encode(implode('', [
            $parte->id,
            $parte->nome,
            $parte->tipo,
        ]));
    }

    private function applyFilter($chaves) {
        $arrayFinal = [];

        foreach ($this->partes as $parte) {
            $chave = $this->generateKey($parte);

            if (in_array($chave, $chaves)) {
                $arrayFinal[] = $parte;

                $chaves = array_diff($chaves, [$chave]);
            }
        }

        return $arrayFinal;
    }

    public function removeDuplicates() {
        $chaves = array_map(array($this, 'generateKey'), $this->partes);

        $chavesFiltradas = array_unique($chaves);

        return $this->applyFilter($chavesFiltradas);
    }

}