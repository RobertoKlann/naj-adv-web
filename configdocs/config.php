<?php

use Sami\Sami;
// Componente desenvolvido pela Sensiolabs, para encontrar arquivos e diretórios http://symfony.com/doc/current/components/finder.html
use Symfony\Component\Finder\Finder; 
 
$iterator = Finder::create()
   ->files()
   ->name('*.php')// todos os arquivos .php
   ->in(__DIR__.'/../app'); // local onde deverá realizar a verificação.
 
$configuration = [
    'theme'     => 'default',
    'title'     => 'naj-adv-web', // Título que será exibido na view HTML
    'build_dir' => __DIR__.'/../public/docs', 
    'cache_dir' => __DIR__.'/cache',
];
 
return new Sami($iterator, $configuration); // O arquivo DEVE retornar uma instancia de Sami.

// COMANDO PARA ATUALIZAR:
// php docs/sami.phar update docs/config.php
// php docs/sami.phar parser docs/config.php
// php docs/sami.phar render docs/config.php
