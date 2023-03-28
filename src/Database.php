<?php
    namespace DevMacb\Poderoso;


    use PDO;
    use PDOException;


    class Database {
        private $table;
        private $connection;

        private static $host;
        private static $name;
        private static $user;
        private static $pass;
        private static $port;
        

        public static function config($host, $name, $user, $pass, $port = 3306) {
            self::$host = $host;
            self::$name = $name;
            self::$user = $user;
            self::$pass = $pass;
            self::$port = $port;
        }

        public function __construct($table = null) {
            $this->table = $table;
            $this->setConnection();
        }

        private function setConnection() {
            try{
                $this->connection = new PDO('mysql:host='.self::$host.';dbname='.self::$name.';port='.self::$port,self::$user,self::$pass);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $e) {
                die('ERROR: '.$e->getMessage());
            }
        }


        public function execute($query,$params = []) {
            try{
                $statement = $this->connection->prepare($query);
                $statement->execute($params);
                return $statement;
            }
            catch(PDOException $e) {
                die('ERROR: '.$e->getMessage());
            }
        }

        public function insert($values) {
            //DADOS DA QUERY
            $fields = array_keys($values);
            $binds  = array_pad([],count($fields),'?');
            //MONTA A QUERY
            $query = 'INSERT INTO '.$this->table.' ('.implode(',',$fields).') VALUES ('.implode(',',$binds).')';
            //EXECUTA O INSERT
            $this->execute($query,array_values($values));
            //RETORNA O ID INSERIDO
            return $this->connection->lastInsertId();
        }


        public function select($where = null, $order = null, $limit = null, $fields = '*'){
            //DADOS DA QUERY
            $where = strlen($where) ? 'WHERE '.$where : '';
            $order = strlen($order) ? 'ORDER BY '.$order : '';
            $limit = strlen($limit) ? 'LIMIT '.$limit : '';
            //MONTA A QUERY
            $query = 'SELECT '.$fields.' FROM '.$this->table.' '.$where.' '.$order.' '.$limit;
            //EXECUTA A QUERY
            return $this->execute($query);
        }


        public function update($where,$values) {
            //DADOS DA QUERY
            $fields = array_keys($values);
            //MONTA A QUERY
            $query = 'UPDATE '.$this->table.' SET '.implode('=?,',$fields).'=? WHERE '.$where;
            //EXECUTAR A QUERY
            $this->execute($query,array_values($values));
            //RETORNA SUCESSO
            return true;
        }


        public function delete($where){
            //MONTA A QUERY
            $query = 'DELETE FROM '.$this->table.' WHERE '.$where;
            //EXECUTA A QUERY
            $this->execute($query);
            //RETORNA SUCESSO
            return true;
        }
    }
?>