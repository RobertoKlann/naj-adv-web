<?php

namespace App;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NajException;
  
abstract class Estrutura {
    
    /**
     * Imprime array formatado
     */
    public static function print_r($content){
        echo "<pre>";
        print_r($content);
        echo "</pre>"; 
    }

    /**
     * Instancia objeto de Resposta (Utilizado para retornar dados para o sistema NAJ DELPHI LEGADO)
     * 
     * @param string $msg  Menssagem
     * @param int    $code Código
     * @param bool   $return define se retorna o resultado
     * @return JSON
     */
    public static function responseNajLegado($msg, $code = 400, $return = false) {
        $response = new \stdClass();
        $response->response = array(new \stdClass());
        //Seta Status Code 400 por default
        $response->response[0]->status_code = $code;
        //Seta Status Message em Branco por Default
        $response->response[0]->status_message = $msg;
        //enconding para UTF8 'JSON_UNESCAPED_UNICODE' 
        if($return){
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }else{
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Response NAJ
     * 
     * @param JSON $content
     * @param int $code 200 default
     * @return JSON
     */
    public static function responseNaj($message, $code = 200){
        //Prepara o response
        $response = new \StdClass();
        $response->code    = $code;
        $response->message = $message;
        //Retorna response em JSON
        //return response()->json($response)->content();
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Response da classe EscavadorConroller
     * 
     * @param JSON $content
     * @param int $code 200 default
     * @return JSON
     */
    public static function responseNajEscavador($content, $code = 200){
        //Prepara o response
        $response = new \StdClass();
        $response->code    = $code;
        $response->content = json_decode($content);
        //Retorna response em JSON
        return response()->json($response)->content();
        //return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
            
    /**
     * Response de Exeption da classe EscavadoConroller
     * 
     * @param object $e Erro/Exceção
     * @param string $nomeArquivoLog O nome do arquivo pode ser informado juntamente com subpastas,por exemplo: escavador/retornarOrigens.
     * Além disso nome do arquivo será concatenado com 'log_' no inicio e data do final.
     * comforme os respectivos exemplos:
     * @example caminho_raiz_do_projeto/storage/logs/escavador/retornarOrigens/log_retornarOrigens_2020-07-08.txt
     * @example log_nomeDoArquvo_2020-07-08.txt
     * @return JSON
     */
    public static function responseNajExeption($e, $nomeArquivoLog){
        //Grava log
        $contexto = "MESSAGE: " . self::Utf8_ansi($e->getMessage()) . " LINE: " . $e->getLine() . " FILE: " . $e->getFile() . " ";
        self::gravaLog($nomeArquivoLog, $contexto);
        //Prepara o response
        $response = new \stdClass();
        $response->code    = $e->getCode();
        $response->message = self::Utf8_ansi($e->getMessage());
        $response->line    = $e->getLine();
        $response->file    = $e->getFile();
        //Retorna response em JSON
        return response()->json($response)->content();
    }
    
    /**
     * Grava log na pasta storage/logs contido na pasta raiz do projeto
     * 
     * @param string $nomeArquivo O nome do arquivo pode ser informado juntamente com subpastas,por exemplo: escavador/retornarOrigens. 
     * Além disso nome do arquivo será concatenado com 'log_' no inicio e data do final.
     * comforme os respectivos exemplos:
     * @example caminho_raiz_do_projeto/storage/logs/escavador/retornarOrigens/log_retornarOrigens_2020-07-08.txt
     * @example log_nomeDoArquvo_2020-07-08.txt
     * @param string $contexto Mensagem que será registarda no arquivo de log
     */
    public static function gravaLog($nomeArquivo, $contexto){
        $pastas      = explode('/',$nomeArquivo);
        //Vamos extarir o nome do arquivo
        $nomeArquivo = $pastas[count($pastas)-1];
        //Vamos verificar se as pastas existem, se não existir vamos cria-las
        $pathRoot = env('APP_DIR') ."storage/logs";
        foreach($pastas as $pasta){
            $pathRoot .= "/" . $pasta;
            if (!is_dir($pathRoot)) {
                mkdir($pathRoot);
            }
        }
        //Caminho do arquivo
        $pathFile = $pathRoot. '/log_' . $nomeArquivo . '_' . date("Y-m-d") . '.txt';
        //Abre arquivo 
        $fileLog  = fopen($pathFile, "a");
        //Grava contexto no arquivo
	    fwrite($fileLog, "[" . date('Y-m-d H:i:s') . "] - " . $contexto . "\n");
        //Fecha arquivo
        fclose($fileLog);
    }
    
    /**
     * Armazena arquivo Json Temporário na pasta public
     * 
     * @param string $fileName
     * @param bool $json_encode
     */
    public static function storeFileTemp($object, $fileName, $json_encode = true){
        //Abre arquivo e apaga conteúdo ou cria um novo caso não exista
        $fileJson  = fopen($fileName . '.js', "w");
        if($json_encode){
            $object = json_encode($object);
        }
        //Grava o JSON da FaturaIUGU
        fwrite($fileJson, $object);
        fclose($fileJson);
    }
    
    /**
     * Carrega arquivo Json Temporário na pasta public
     * 
     * @param string $fileName
     * @param bool $json_decode
     * @return JSON
     * @throws NajException
     */
    public static function loadFileTemp($fileName, $json_decode = true){
        $sFilePath = public_path() . '\\' . $fileName . '.js';
        if (!file_exists($sFilePath)) {
            return null;
        } 
        $fileJson  = fopen($fileName . '.js', "r");
        if(!$fileJson){
            throw new NajException("Erro ao carregar o arqivo temporário " . $fileName . ".js em " . public_path());
        }
        $xDados    = fread($fileJson, filesize($sFilePath));
        if($json_decode){
            $xDados = json_decode($xDados);
        }
        if(!$xDados){
            throw new NajException("O arquivo temporário " . $fileName . ".js em " . public_path() . " não contém dados!");
        }
        fclose($fileJson);
        return $xDados; 
    }
    
    /**
     * Deleta arquivo Json Temporário na pasta public
     * 
     * @param string $fileName
     * @return bool
     */
    public static function destroyFileTemp($fileName = 'FaturaIUGU'){
        $sFilePath = public_path() . '\\' . $fileName . '.js';
        if (file_exists($sFilePath)) {
            if (!unlink($sFilePath)) {
                throw new NajException("Erro ao deletar o arqivo temporário " . $fileName . ".js em " . public_path());
            }
        } 
    }
    
    /**
     * Grava Cache 
     * 
     * @param string $nomeArquivo o nome do arquivo será concatenado com 'cache' no inicio, ex: cacheNomeDoArquvo.
     * @param string $contexto mensagem que será registarda no arquivo de cache
     */
    public static function storeCache($nomeArquivo, $contexto){
        $sPathCache = env('APP_DIR') ."storage/cache";
        if (!is_dir($sPathCache)) {
            mkdir($sPathCache);
            $sFilePath = $sPathCache . "/" . $nomeArquivo;
            if (!is_dir($sFilePath)) {
                mkdir($sFilePath);
            }
        }
        //Abre arquivo novamente 
        $fileJson = fopen(env('APP_DIR') .'storage/cache/' . $nomeArquivo . '/cache_' . $nomeArquivo . '.txt', "w");
	fwrite($fileJson, $contexto . "\n");
        fclose($fileJson);
    }
    
    /**
     * Carrega arquivo cache na pasta storage/cache
     * 
     * @param string $fileName
     * @param bool $json_decode
     * @return JSON
     * @throws NajException
     */
    public static function loadCache($fileName, $json_decode = true){
        //Diretório do arquivo 
        $sFilePath = env('APP_DIR') ."storage/cache/" . $fileName . "/cache_" . $fileName . ".txt";
        //verifica se arquivo existe no diretório
        if (!file_exists($sFilePath)) {
            return null;
        } 
        //abre o arquivo
        $fileJson = fopen($sFilePath, "r");
        //Verifica se carregou arquivo
        if(!$fileJson){
            throw new NajException("Erro ao carregar o arqivo de cache " . $fileName . ".js em " . $sFilePath);
        }
        //Extrai dados do arquivo
        $xDados = fread($fileJson, filesize($sFilePath));
        //Se tipo de leitura for json_decode retorna dados decodados
        if($json_decode){
            $xDados = json_decode($xDados);
        }
        if(!$xDados){
            throw new NajException("O arquivo de cache " . $fileName . ".js em " . $sFilePath . " não contém dados!");
        }
        fclose($fileJson);
        return $xDados; 
    }

    /**
     * Valida CPF
     * 
     * @param string $cpf
     * @return boolean
     */
    public static function validaCPF($cpf) {
        // Extrai somente os números
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }
        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf{$c} * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf{$c} != $d) {
                return false;
            }
        }
        return true;
    }

    /**
     * Valida CNPJ
     * 
     * @param string $cnpj
     * @return boolean
     */
    public static function validaCNPJ($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;
        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj))
            return false;
        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj{$i} * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto))
            return false;
        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj{$i} * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
    }

    /**
     * Valida CEP
     * 
     * @param type $cep
     * @return type
     */
    public static function validaCEP($cep) {
        return preg_match('/([0-9]{2,2}[.]?[0-9]{3})([-]?[0-9]{3})?$/', $cep);
    }

    /**
     * Valida telefone
     * 
     * @param string $tel
     */
    public static function validaTelefone($tel) {
        return preg_match('/^\([0-9]{2,3}\)?\s?[0-9]{4,5}-[0-9]{4}$/', $tel);
    }

    /**
     * Extrai a parte inteira do prefixo do telefone
     * 
     * @param type $tel
     * @return string
     */
    public static function extraiPrefixoTelefone($tel) {
        if (!empty(stristr($tel, '(')) && !empty(stristr($tel, ')'))) {
            $index = strpos($tel, ')');
            $prefixo = substr($tel, 1, $index - 1);
        } else {
            $index = strpos($tel, '-');
            $prefixo = substr($tel, 0, $index);
        }
        return $prefixo;
    }
    
    /**
     * Extrai a parte inteira do numero do telefone
     * 
     * @param type $tel
     * @return string
     */
    public static function extraiNumeroTelefone($tel) {
        while(!empty(strstr($tel, ' '))){
            $tel = str_replace(' ', '', $tel);
        }
        if (!empty(stristr($tel, '(')) && !empty(stristr($tel, ')'))) {
            $index = strpos($tel, ')');
        } else {
            $index = strpos($tel, '-');
        }
        $numero = substr($tel, $index + 1);
        return str_replace('-', '', $numero);                
    }
    
    /**
     * Remove Cifrão
     * 
     * @param string $word
     * @return string
     */      
    public static function removeCifrao($word){
        return str_replace("R$ ","",$word);
    }
    
    /**
     * Obtêm registro da tabela sys_config
     * 
     * @param string $secao valor da 'SECAO'
     * @param string $chave valor da 'CHAVE'
     * @return registro
     * @throws NajException
     */
    public static function getRecordSysConfig($secao, $chave){
        $valor = DB::table('sys_config')
                ->select('sys_config.VALOR')
                ->where('sys_config.SECAO', $secao)
                ->where('sys_config.CHAVE', $chave)
                ->first();
        
        if($valor){
            return $valor->VALOR;
        }else{
            throw new NajException("O parâmetro Seção: {$secao} Chave: {$chave} não foi definido na tabela sys_config do DB");
        }
        
    }
    
    /**
     * Convert "\n" to "" empty char
     * 
     * @param type $valor
     * @return string
     */
    public static function replaceNb($valor){
        return strtr($valor, ["\n"=>""]);
    }
    
    /**
     * Convert Aspas Duplas para Aspas Simples 
     * 
     * @param string $valor
     * @return string
     */
    public static function replaceAspasDuplas($valor){
        return strtr($valor, ["\""=>"'"]);
    }
    
    /**
     * Convert Aspas Duplas para Aspas Simples 
     * 
     * @param string $valor
     * @return string
     */
    public static function replaceAspasSimples($valor){
        return strtr($valor, ["'"=>"\""]);
    }
    
    /**
     * Convert "." to "" empty char
     * 
     * @param string $valor
     * @return string
     */
    public static function replaceDot($valor){
        return strtr($valor, ["."=>""]);
    }
    
    /**
     * Convert "�" to "" empty char
     * 
     * @param string $valor
     * @return string
     */
    public static function replaceUnicodeBlock($valor){
        return str_replace('�', '', $valor);
    }
    
    /**
     * Seta o time zone 'America/Sao_Paulo'
     */
    public static function setaTimeZone(){
        //Seta a Time Zone
        date_default_timezone_set('America/Sao_Paulo');
    } 
    
    /**
     * Convert unicode to char
     * 
     * @param stirng $valor
     * @return stirng
     */
    public static function Utf8_ansi($valor='') {
        $utf8_ansi2 = array(
        "\u00c0" =>"À",
        "\u00c1" =>"Á",
        "\u00c2" =>"Â",
        "\u00c3" =>"Ã",
        "\u00c4" =>"Ä",
        "\u00c5" =>"Å",
        "\u00c6" =>"Æ",
        "\u00c7" =>"Ç",
        "\u00c8" =>"È",
        "\u00c9" =>"É",
        "\u00ca" =>"Ê",
        "\u00cb" =>"Ë",
        "\u00cc" =>"Ì",
        "\u00cd" =>"Í",
        "\u00ce" =>"Î",
        "\u00cf" =>"Ï",
        "\u00d1" =>"Ñ",
        "\u00d2" =>"Ò",
        "\u00d3" =>"Ó",
        "\u00d4" =>"Ô",
        "\u00d5" =>"Õ",
        "\u00d6" =>"Ö",
        "\u00d8" =>"Ø",
        "\u00d9" =>"Ù",
        "\u00da" =>"Ú",
        "\u00db" =>"Û",
        "\u00dc" =>"Ü",
        "\u00dd" =>"Ý",
        "\u00df" =>"ß",
        "\u00e0" =>"à",
        "\u00e1" =>"á",
        "\u00e2" =>"â",
        "\u00e3" =>"ã",
        "\u00e4" =>"ä",
        "\u00e5" =>"å",
        "\u00e6" =>"æ",
        "\u00e7" =>"ç",
        "\u00e8" =>"è",
        "\u00e9" =>"é",
        "\u00ea" =>"ê",
        "\u00eb" =>"ë",
        "\u00ec" =>"ì",
        "\u00ed" =>"í",
        "\u00ee" =>"î",
        "\u00ef" =>"ï",
        "\u00f0" =>"ð",
        "\u00f1" =>"ñ",
        "\u00f2" =>"ò",
        "\u00f3" =>"ó",
        "\u00f4" =>"ô",
        "\u00f5" =>"õ",
        "\u00f6" =>"ö",
        "\u00f8" =>"ø",
        "\u00f9" =>"ù",
        "\u00fa" =>"ú",
        "\u00fb" =>"û",
        "\u00fc" =>"ü",
        "\u00fd" =>"ý",
        "\u00ff" =>"ÿ",
        "\u0096" =>"");

        return self::replaceNb(strtr($valor, $utf8_ansi2));      
    }
    
    /**
     * Convert "\n" to "" empty char
     * 
     * @param type $valor
     * @return type
     */
    public static function replaceCaracteresEspeciais($valor){
        $caracteresEspeciais = array(
        "<" =>"_",
        ">" =>"_",
        "!" =>"_",
        "@" =>"_",
        "#" =>"_",
        "$" =>"_",
        "%" =>"_",
        "*" =>"_",
        "," =>"_",
        "." =>"_",
        "(" =>"_",
        ")" =>"_",
        ")" =>"_",
        ")" =>"_",
        "+" =>"_",
        "=" =>"_",
        "{" =>"_",
        "}" =>"_",
        "[" =>"_",
        "?" =>"_",
        ";" =>"_",
        ":" =>"_",
        "|" =>"_",
        "/" =>"_",
        '"' =>"_",
        "~" =>"_",
        "^" =>"_",
        "¨" =>"_",
        "æ" =>"_",
        "Æ" =>"_",
        "ø" =>"_",
        "£" =>"_",
        "Ø" =>"_",
        "ƒ" =>"_",
        "ª" =>"_",
        "º" =>"_",
        "¿" =>"_",
        "®" =>"_",
        "¼" =>"_",
        "ß" =>"_",
        "ß" =>"_",
        "µ" =>"_",
        "þ" =>"_",
        "ý" =>"_",
        "Ý" =>"_",
        "´" =>"_",
        "`" =>"_",
        "" =>"_");
        return self::replaceUnderline(strtr($valor, $caracteresEspeciais));
    }
    
    /**
     * Substitui caracteres com trema por caracteres sem trema
     * 
     * @param type $valor
     * @return string
     */
    public static function replaceTrema($valor){
        //ä,Ä,ë,Ë,ï,Ï,ö,Ö,ü,Ü
        $underline = array(
        "ä" => "a",
        "Ä" => "A",
        "ë" => "e",
        "Ë" => "E",
        "ï" => "i",
        "Ï" => "I",
        "ö" => "o",
        "Ö" => "O",
        "ü" => "u",
        "Ü" => "U");
        return strtr($valor, $underline);
    }
    
    /**
     * Convert multiples "_" chars to unique "_" char
     * 
     * @param type $valor
     * @return string
     */
    public static function replaceMultipleUnderline($valor){
        $underline = array(
        "_"       => "_",
        " _ "     => "_",
        "__"      => "_",
        " __ "    => "_",
        "___"     => "_",
        " ___ "   => "_",
        "____"    => "_",
        " ____ "  => "_",
        "_____"   => "_",
        " _____ " => "_");
        return strtr($valor, $underline);
    }
    
     /**
     * Convert underline char "_" to empty char ""
     * 
     * @param type $valor
     * @return type
     */
    public static function replaceUnderline($valor){
        $underline = array(
            "_" =>""
        );
        return strtr($valor, $underline);
    }
    
    /**
     * Remove Formatação do Número do Processo
     * 
     * @param string $valor
     * @return string
     */
    public static function removeFormatacaoNumeroProcesso($valor){
        $valor =  strtr($valor, ["-"=>""]);
        return strtr($valor, ["."=>""]);
    }
    
    /**
     * Escreve número por extenso
     * 
     * @param int $valor
     * @param bool $bolExibirMoeda default false
     * @param bool $bolPalavraFeminina default false
     * @return string
     */
    public static function valorPorExtenso( $valor = 0, $bolExibirMoeda = false, $bolPalavraFeminina = false )    {
 
        //$valor = self::removerFormatacaoNumero( $valor );
 
        $singular = null;
        $plural   = null;
 
        if ( $bolExibirMoeda ){
            $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural   = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
        } else {
            $singular = array("", "", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural   = array("", "", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
        }
 
        $c   = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
        $d   = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
        $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezessete", "dezoito", "dezenove");
        $u   = array("", "um", "dois", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
 
 
        if ( $bolPalavraFeminina ){
        
            if ($valor == 1){
                $u = array("", "uma", "duas", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
            } else {
                $u = array("", "um", "duas", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
            }
            
            $c = array("", "cem", "duzentas", "trezentas", "quatrocentas","quinhentas", "seiscentas", "setecentas", "oitocentas", "novecentas");
            
        }
 
        $z = 0;
 
        $valor   = number_format( $valor, 2, ".", "." );
        $inteiro = explode( ".", $valor );
 
        for ( $i = 0; $i < count( $inteiro ); $i++ ){
            for ( $ii = mb_strlen( $inteiro[$i] ); $ii < 3; $ii++ ){
                $inteiro[$i] = "0" . $inteiro[$i];
            }
        }
 
        // $fim identifica onde que deve se dar junção de centenas por "e" ou por "," ;)
        $rt = null;
        $fim = count( $inteiro ) - ($inteiro[count( $inteiro ) - 1] > 0 ? 1 : 2);
        for ( $i = 0; $i < count( $inteiro ); $i++ ){
            $valor = $inteiro[$i];
            $rc    = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
            $rd    = ($valor[1] < 2) ? "" : $d[$valor[1]];
            $ru    = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
 
            $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
            $t = count( $inteiro ) - 1 - $i;
            $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
            if ( $valor == "000")
                $z++;
            elseif ( $z > 0 )
                $z--;
            if ( ($t == 1) && ($z > 0) && ($inteiro[0] > 0) )
                $r .= ( ($z > 1) ? " de " : "") . $plural[$t];
            if ( $r )
                $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
        }
 
        $rt = mb_substr( $rt, 1 );
 
        return($rt ? trim( $rt ) : "zero");
 
    }
    
    /**
     * Passando data do formato americano para formato brasileiro
     * 
     * @param string $data
     * @return string
     */
    public function ConverteDataAmericanoParaBrasileiro($data){
        return implode('/', array_reverse(explode('-', $data)));
    }
    
    /**
     * Passando data do formato brasileiro para formato americano 
     * 
     * @param string $data
     * @return string
     */
    public function ConverteDataBrasileiroParaAmericano($data){
        return implode('-', array_reverse(explode('/', $data)));
    }
    
}
