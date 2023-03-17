<?php 
    namespace DevMacB;


    use PDO;
    use PDOException;
    

    class BancoDados {
        private PDO $conexao;
        private string $tabela;

        private static int $porta;
        private static string $driver;
        private static string $host;
        private static string $nome_banco;
        private static string $usuario;
        private static string $senha;
      
    
        public function __construct($tabela = null){
            $this->tabela = $tabela;
            $this->conectar();
        }


        public static function configurar($driver, $host, $porta, $nome_banco, $usuario, $senha) {
            self::$driver     = $driver;
            self::$host       = $host;
            self::$porta      = $porta;
            self::$nome_banco = $nome_banco;
            self::$usuario    = $usuario;
            self::$senha      = $senha;
        }
    

        private function conectar() {
            try {
                $dsn = self::$driver.':'.self::$host.';dbname='.self::$nome_banco.';port='.self::$porta;

                $this->conexao = new PDO($dsn, self::$usuario, self::$senha);
                $this->conexao->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
                $this->conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conexao->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                $this->conexao->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8");
            }
            catch(PDOException $erro) {
                die('ERRO: '.$erro->getMessage());
            }
        }
    

        public function executar($query, $parametros = []){
            try {
                $declaracao = $this->conexao->prepare($query);
                $declaracao->execute($parametros);
                return $declaracao;
            }
            catch(PDOException $erro){
                die('ERRO: '.$erro->getMessage());
            }
        }
    

        public function inserir($valores) {
            $campos   = array_keys($valores);
            $valores  = array_pad([], count($campos), '?');
            $query    = 'INSERT INTO '.$this->tabela.' ('.implode(',',$campos).') valores ('.implode(',',$valores).')';
            $this->executar($query, array_values($valores));
            return $this->conexao->lastInsertId();
        }
 

        public function selecionar($where = null, $order = null, $limit = null, $fields = '*'){
            $where = strlen($where) ? 'WHERE '.$where    : '';
            $order = strlen($order) ? 'ORDER BY '.$order : '';
            $limit = strlen($limit) ? 'LIMIT '.$limit    : '';
            $query = 'SELECT '.$fields.' FROM '.$this->tabela.' '.$where.' '.$order.' '.$limit;
            return $this->executar($query);
        }
    
      
        public function atualizar($where, $valores) {
            $campos = array_keys($valores);
            $query = 'UPDATE '.$this->tabela.' SET '.implode('=?,',$campos).'=? WHERE '.$where;
            $this->executar($query,array_values($valores));
            return true;
        }
    

        public function delete($where) {
            $query = 'DELETE FROM '.$this->tabela.' WHERE '.$where;
            $this->executar($query);
            return true;
        }
    }
?>