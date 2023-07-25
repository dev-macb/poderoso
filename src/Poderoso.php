<?php
    namespace MacB;

    use PDO;
    use PDOStatement;
    use PDOException;

    class Poderoso {
        private PDO $conexao;
        private string $tabela;
        
        private static string $driver;
        private static string $host_alvo;
        private static int $porta_alvo;
        private static string $banco_dados;
        private static string $nome_usuario;
        private static string $senha_usuario;
            

        public function __construct(string $tabela = null) {
            $this->tabela = $tabela;
            $this->conectar();
        }


        public static function configurar(string $driver, string $host_alvo, int $porta_alvo, string $banco_dados, string $nome_usuario, string $senha_usuario): void {
            self::$driver = $driver;
            self::$host_alvo = $host_alvo;
            self::$porta_alvo = $porta_alvo;
            self::$banco_dados = $banco_dados;
            self::$nome_usuario = $nome_usuario;
            self::$senha_usuario = $senha_usuario;
        }


        private function conectar(): void {
            try {
                $dsn = self::$driver . ':host=' . self::$host_alvo . ';dbname=' . self::$banco_dados . ';port=' . self::$porta_alvo;
                $this->conexao = new PDO($dsn, self::$nome_usuario, self::$senha_usuario);
                $this->conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $erro) {
                die('Erro ao conectar ao banco de dados: ' . $erro->getMessage());
            }
        }


        public function executar(string $query, array $parametros = []): PDOStatement {
            try {
                $declaracao = $this->conexao->prepare($query);
                $declaracao->execute($parametros);
                return $declaracao;
            } catch (PDOException $erro) {
                die('Erro ao executar query no banco de dados: ' . $erro->getMessage());
            }
        }


        public function inserir(array $variaveis): int {
            $campos  = array_keys($variaveis);
            $valores = array_pad([], count($campos), '?');
            $query   = 'INSERT INTO ' . $this->tabela . ' (' . implode(',', $campos) . ') VALUES (' . implode(',', $valores) . ')';
            $this->executar($query, array_values($variaveis));
            return (int) $this->conexao->lastInsertId();
        }


        public function selecionar(?string $where = null, ?string $order = null, ?int $limit = null, string $fields = '*'): PDOStatement {
            $where = $where ? 'WHERE ' . $where : '';
            $order = $order ? 'ORDER BY ' . $order : '';
            $limit = $limit ? 'LIMIT ' . $limit : '';
            $query = 'SELECT ' . $fields . ' FROM ' . $this->tabela . ' ' . $where . ' ' . $order . ' ' . $limit;
            return $this->executar($query);
        }


        public function atualizar(string $where, array $valores): bool {
            $campos = array_keys($valores);
            $query = 'UPDATE ' . $this->tabela . ' SET ' . implode('=?, ', $campos) . '=? WHERE ' . $where;
            $this->executar($query, array_values($valores));
            return true;
        }


        public function deletar(string $where): bool {
            $query = 'DELETE FROM ' . $this->tabela . ' WHERE ' . $where;
            $this->executar($query);
            return true;
        }


        public function contar(?string $where = null): int {
            $where = $where ? 'WHERE ' . $where : '';
            $query = 'SELECT COUNT(*) as total FROM ' . $this->tabela . ' ' . $where;
            $result = $this->executar($query)->fetch(PDO::FETCH_ASSOC);
            return (int) $result['total'];
        }


        public function buscar(string $where): ?array {
            $query = 'SELECT * FROM ' . $this->tabela . ' WHERE ' . $where;
            return $this->executar($query)->fetch(PDO::FETCH_ASSOC) ?: null;
        }


        public function buscar_registros(?string $where = null, ?string $order = null, ?int $limit = null, string $fields = '*'): array {
            $where = $where ? 'WHERE '.$where : '';
            $order = $order ? 'ORDER BY '.$order : '';
            $limit = $limit ? 'LIMIT '.$limit : '';
            $query = 'SELECT '.$fields.' FROM '.$this->tabela.' '.$where.' '.$order.' '.$limit;
            return $this->executar($query)->fetchAll(PDO::FETCH_ASSOC);
        }
    }
?>