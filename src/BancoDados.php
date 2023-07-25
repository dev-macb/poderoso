<?php
    namespace DevMacb\Poderoso;


    use PDO;
    use PDOException;


    class BancoDados {
        private $tabela;
        private $conexao;

        private static $host;
        private static $name;
        private static $user;
        private static $pass;
        private static $port;
        

        public function __construct($tabela = null) {
            $this->tabela = $tabela;
            $this->conectar();
        }


        public static function configurar($host, $name, $user, $pass, $port = 3306) {
            self::$host = $host;
            self::$name = $name;
            self::$user = $user;
            self::$pass = $pass;
            self::$port = $port;
        }


        private function conectar() {
            try{
                $dsn = 'mysql:host='.self::$host.';dbname='.self::$name.';port='.self::$port;
                $this->conexao = new PDO($dsn, self::$user, self::$pass);
                $this->conexao->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $erro) {
                die('ERROR: '.$erro->getMessage());
            }
        }


        public function executar($query, $parametros = []) {
            try{
                $declaracao = $this->conexao->prepare($query);
                $declaracao->execute($parametros);
                return $declaracao;
            }
            catch(PDOException $erro) {
                die('ERRO: '.$erro->getMessage());
            }
        }

        public function inserir($variaveis) {
            $campos  = array_keys($variaveis);
            $valores = array_pad([], count($campos), '?');
            $query   = 'INSERT INTO ' . $this->tabela . ' (' . implode(',', $campos) . ') VALUES (' . implode(',', $valores) . ')';
            $this->executar($query, array_values($variaveis));
            return $this->conexao->lastInsertId();
        }


        public function selecionar($where = null, $order = null, $limit = null, $fields = '*'){
            $where = strlen($where) ? 'WHERE '.$where : '';
            $order = strlen($order) ? 'ORDER BY '.$order : '';
            $limit = strlen($limit) ? 'LIMIT '.$limit : '';
            $query = 'SELECT '.$fields.' FROM '.$this->tabela.' '.$where.' '.$order.' '.$limit;
            return $this->executar($query);
        }


        public function atualizar($where, $valores) {
            $campos = array_keys($valores);
            $query = 'UPDATE ' . $this->tabela . ' SET ' . implode('=?,',$campos) . '=? WHERE ' . $where;
            $this->executar($query, array_values($valores));
            return true;
        }


        public function deletar($where) {
            $query = 'DELETE FROM ' . $this->tabela . ' WHERE ' . $where;
            $this->executar($query);
            return true;
        }
    }
?>