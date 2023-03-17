<?php 
    namespace DevMacb;
    

    class Paginacao {
        private int $limite;
        private int $resultados;
        private int $paginas;
        private int $pagina_atual;
    

        public function __construct($resultados, $pagina_atual = 1, $limite = 10) {
            $this->limite       = $limite;
            $this->resultados   = $resultados;
            $this->pagina_atual = (is_numeric($pagina_atual) and $pagina_atual > 0) ? $pagina_atual : 1;
            $this->calcular();
        }
    

        private function calcular() {
            $this->paginas      = $this->resultados > 0 ? ceil($this->resultados / $this->limite) : 1;
            $this->pagina_atual = $this->pagina_atual <= $this->paginas ? $this->pagina_atual : $this->paginas;
        }
    

        public function obter_limite() {
            $offset = $this->limite * ($this->pagina_atual - 1);
            return $offset.','.$this->limite;
        }
    

        public function obter_pagina(){
            if($this->paginas == 1) return [];
            
            $paginas = [];
            for($i = 1; $i <= $this->paginas; $i++) {
                $paginas[] = [
                    'page'    => $i,
                    'current' => $i == $this->pagina_atual
                ];
            }
            return $paginas;
        }
    }
?>