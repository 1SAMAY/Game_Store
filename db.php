<?php
require_once __DIR__ . '/config.php';

class AppResult
{
    public array $rows;
    public int $num_rows;
    private int $position = 0;

    public function __construct(array $rows = [])
    {
        $this->rows = array_values($rows);
        $this->num_rows = count($this->rows);
    }

    public function fetch_assoc()
    {
        if ($this->position >= $this->num_rows) {
            return false;
        }

        return $this->rows[$this->position++];
    }
}

class AppStatement
{
    private AppDatabase $db;
    private string $sql;
    private ?PDOStatement $stmt = null;
    private array $boundValues = [];
    public string $error = '';
    public int $insert_id = 0;

    public function __construct(AppDatabase $db, string $sql)
    {
        $this->db = $db;
        $this->sql = $sql;
        try {
            $this->stmt = $db->pdo()->prepare($sql);
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function isValid(): bool
    {
        return $this->stmt instanceof PDOStatement;
    }

    public function bind_param(string $types, ...$vars): bool
    {
        $this->boundValues = array_values($vars);

        return true;
    }

    public function execute(array $params = null): bool
    {
        if (!$this->stmt) {
            return false;
        }

        $values = $params ?? [];
        if ($params === null) {
            $values = $this->boundValues;
        }

        try {
            $ok = $this->stmt->execute(array_values($values));
            $this->insert_id = (int) $this->db->pdo()->lastInsertId();
            return $ok;
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function get_result()
    {
        if (!$this->stmt) {
            return false;
        }

        try {
            $rows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            return new AppResult($rows ?: []);
        } catch (Throwable $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function close(): void
    {
        $this->stmt = null;
    }
}

class AppDatabase
{
    private PDO $pdo;
    public string $connect_error = '';

    public function __construct()
    {
        $db = app_db_config();
        try {
            if (!empty($db['url'])) {
                $dsn = $this->dsnFromUrl($db['url']);
                $username = rawurldecode((string) parse_url($db['url'], PHP_URL_USER));
                $password = rawurldecode((string) parse_url($db['url'], PHP_URL_PASS));
            } elseif ($db['driver'] === 'pgsql') {
                $dsn = sprintf(
                    'pgsql:host=%s;port=%d;dbname=%s;sslmode=%s',
                    $db['host'],
                    $db['port'],
                    $db['database'],
                    $db['sslmode']
                );
                $username = $db['username'];
                $password = $db['password'];
            } else {
                $dsn = sprintf(
                    '%s:host=%s;port=%d;dbname=%s;charset=%s',
                    $db['driver'],
                    $db['host'],
                    $db['port'],
                    $db['database'],
                    $db['charset']
                );
                $username = $db['username'];
                $password = $db['password'];
            }

            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => true,
            ]);
        } catch (Throwable $e) {
            $this->connect_error = $e->getMessage();
            throw $e;
        }
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function set_charset(string $charset): void
    {
        // No-op for PDO. Kept for mysqli compatibility.
    }

    public function prepare(string $sql)
    {
        $statement = new AppStatement($this, $sql);
        return $statement->isValid() ? $statement : false;
    }

    public function query(string $sql)
    {
        try {
            $stmt = $this->pdo->query($sql);
            if ($stmt === false) {
                return false;
            }

            if ($stmt->columnCount() > 0) {
                return new AppResult($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
            }

            return true;
        } catch (Throwable $e) {
            $this->connect_error = $e->getMessage();
            return false;
        }
    }

    public function real_escape_string(string $value): string
    {
        return str_replace(
            ["\\", "\0", "\n", "\r", "\x1a", "'", '"'],
            ["\\\\", "\\0", "\\n", "\\r", "\\Z", "''", '""'],
            $value
        );
    }

    private function dsnFromUrl(string $url): string
    {
        $parts = parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            throw new RuntimeException('Invalid DATABASE_URL');
        }

        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $port = isset($parts['port']) ? (int) $parts['port'] : 5432;
        $path = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        if ($scheme === 'postgres' || $scheme === 'postgresql') {
            $scheme = 'pgsql';
        }

        $dsn = sprintf('%s:host=%s;port=%d;dbname=%s', $scheme, $host, $port, $path ?: 'postgres');
        if (!empty($query['sslmode'])) {
            $dsn .= ';sslmode=' . $query['sslmode'];
        }

        return $dsn;
    }
}

try {
    $conn = new AppDatabase();
} catch (Throwable $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>
