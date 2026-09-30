<?php
class ArcadeDatabaseResult {
    public $num_rows = 0;
    private $rows = [];
    private $index = 0;
    public function __construct(array $rows) { $this->rows = $rows; $this->num_rows = count($rows); }
    public function fetch_assoc() { return $this->index < $this->num_rows ? $this->rows[$this->index++] : null; }
    public function fetch_array() { return $this->fetch_assoc(); }
}

class ArcadeDatabase {
    private $pdo;
    public $connect_errno = 0;
    public $error = '';
    public $insert_id = 0;

    public function __construct(array $config = []) {
        $url = getenv('DATABASE_URL')
            ?: getenv('POSTGRES_URL')
            ?: getenv('POSTGRES_URL_NON_POOLING')
            ?: getenv('NEON_DATABASE_URL')
            ?: getenv('SUPABASE_DB_URL');
        try {
            if ($url) {
                $parts = parse_url($url);
                $host = $parts['host'] ?? 'localhost';
                $port = $parts['port'] ?? 5432;
                $name = isset($parts['path']) ? rawurldecode(ltrim($parts['path'], '/')) : '';
                $user = isset($parts['user']) ? rawurldecode($parts['user']) : '';
                $pass = isset($parts['pass']) ? rawurldecode($parts['pass']) : '';
            } else {
                $host = $config['host'] ?? 'localhost';
                $port = $config['port'] ?? 5432;
                $name = $config['name'] ?? '';
                $user = $config['user'] ?? '';
                $pass = $config['pass'] ?? '';
            }
            $dsn = "pgsql:host=" . $host . ";port=" . $port . ";dbname=" . $name . ";sslmode=require;connect_timeout=5";
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (Throwable $e) {
            $this->connect_errno = 1;
            $this->error = $e->getMessage();
        }
    }

    private function normalizeSql($sql) {
        $sql = preg_replace('/'.chr(96).'([^'.chr(96).']*)'.chr(96).'/', '"$1"', $sql);
        $sql = preg_replace('/\bRAND\(\)/i', 'RANDOM()', $sql);
        $sql = preg_replace('/\s+AFTER\s+"[^"]+"/i', '', $sql);

        $sql = preg_replace('/\\bAS\\s+UNSIGNED\\b/i', 'AS INTEGER', $sql);

        if (preg_match('/^\s*SHOW\s+COLUMNS\s+FROM\s+"([^"]+)"\s+LIKE\s+\'([^\']+)\'/i', $sql, $m)) {
            $table = $m[1];
            $column = str_replace("'", "''", $m[2]);
            return "SELECT column_name AS Field, data_type AS Type, is_nullable AS Null, column_default AS Default FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = '" . $table . "' AND column_name = '" . $column . "'";
        }

        $sql = preg_replace('/\bTINYINT\s*\(\s*1\s*\)/i', 'SMALLINT', $sql);
        $sql = preg_replace('/\bINT\s*\(\s*\d+\s*\)/i', 'INTEGER', $sql);
        return $sql;
    }

    public function query($sql, $mode = null) {
        if (!$this->pdo) return false;
        try {
            $sql = $this->normalizeSql($sql);
            $trimmed = ltrim($sql);
            if (preg_match('/^\s*(SELECT|WITH|SHOW)\b/i', $trimmed)) {
                $stmt = $this->pdo->query($sql);
                return new ArcadeDatabaseResult($stmt->fetchAll(PDO::FETCH_ASSOC));
            }
            $this->pdo->query($sql);
            if (preg_match('/^\s*INSERT\b/i', $trimmed)) $this->insert_id = (int) $this->pdo->lastInsertId();
            return true;
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function real_escape_string($value) {
        if (!$this->pdo) return addslashes($value);
        return substr($this->pdo->quote((string)$value), 1, -1);
    }

    public function set_charset($charset) { return true; }

    public function begin_transaction() {
        if (!$this->pdo) return false;
        try { return $this->pdo->beginTransaction(); } catch (Throwable $e) { $this->error = $e->getMessage(); return false; }
    }

    public function commit() {
        if (!$this->pdo) return false;
        try { return $this->pdo->commit(); } catch (Throwable $e) { $this->error = $e->getMessage(); return false; }
    }

    public function rollback() {
        if (!$this->pdo) return false;
        try { return $this->pdo->rollBack(); } catch (Throwable $e) { $this->error = $e->getMessage(); return false; }
    }

    public function close() {
        $this->pdo = null;
        return true;
    }
}
