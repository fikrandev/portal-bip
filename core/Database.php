<?php
/**
 * Portal BIP - Database Wrapper
 * 
 * Singleton PDO wrapper with convenient query methods.
 * All queries use prepared statements to prevent SQL injection.
 */

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    /**
     * Private constructor (singleton pattern)
     */
    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            $errorMsg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            die("
                <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; max-width: 650px; margin: 50px auto; padding: 28px; border: 1px solid #fca5a5; background: #fff5f5; border-radius: 16px; color: #991b1b; box-shadow: 0 10px 25px rgba(0,0,0,0.05);'>
                    <h3 style='margin-top: 0; font-size: 18px; color: #b91c1c;'>⚠️ Koneksi Database MySQL Gagal</h3>
                    <p style='font-size: 14px; line-height: 1.5;'>Portal BIP tidak dapat terhubung ke server database MySQL.</p>
                    <div style='background: #ffffff; padding: 14px; border-radius: 10px; border: 1px solid #fecaca; font-family: monospace; font-size: 12px; word-break: break-all; margin: 14px 0; color: #b91c1c;'>
                        <strong>Detail Error:</strong><br>{$errorMsg}
                    </div>
                    <div style='font-size: 13px; line-height: 1.6; color: #7f1d1d; background: #fee2e2; padding: 14px; border-radius: 10px;'>
                        <strong>Langkah Pemeriksaan di Server VPS:</strong>
                        <ol style='margin: 6px 0 0 18px; padding: 0;'>
                            <li>Pastikan service MySQL / MariaDB sudah berjalan (<code>sudo systemctl status mysql</code>).</li>
                            <li>Buka file <strong>config/database.php</strong> atau <strong>.env</strong>, pastikan <code>DB_NAME</code>, <code>DB_USER</code>, dan <code>DB_PASS</code> sudah sesuai.</li>
                            <li>Pastikan database sudah di-import di server VPS.</li>
                        </ol>
                    </div>
                </div>
            ");
        }
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get raw PDO connection
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Execute a query with parameters
     * 
     * @param string $sql  SQL query with placeholders
     * @param array  $params  Bound parameters
     * @return PDOStatement
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw $e;
        }
    }

    /**
     * Fetch a single row
     */
    public function find(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Fetch all rows
     */
    public function findAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Insert a row and return the last insert ID
     * 
     * @param string $table  Table name
     * @param array  $data   Associative array [column => value]
     * @return string Last insert ID
     */
    public function insert(string $table, array $data): string
    {
        $columns = implode(', ', array_map(fn($col) => "`$col`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, array_values($data));
        return $this->pdo->lastInsertId();
    }

    /**
     * Get last inserted ID
     */
    public function lastInsertId(?string $name = null): string
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * Update rows
     * 
     * @param string $table      Table name
     * @param array  $data       Associative array [column => value]
     * @param string $where      WHERE clause (e.g., "id = ?")
     * @param array  $whereParams  Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setClauses = implode(', ', array_map(fn($col) => "`$col` = ?", array_keys($data)));
        $sql = "UPDATE `{$table}` SET {$setClauses} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Delete rows
     * 
     * @param string $table      Table name
     * @param string $where      WHERE clause
     * @param array  $whereParams  Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->query($sql, $whereParams);
        return $stmt->rowCount();
    }

    /**
     * Count rows
     */
    public function count(string $table, string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM `{$table}` WHERE {$where}";
        $result = $this->find($sql, $params);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }
}
