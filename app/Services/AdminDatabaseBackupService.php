<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AdminDatabaseBackupService
{
    /**
     * Write a SQL dump of the current database contents.
     *
     * @param resource $output
     */
    public function writeTo($output, string $backupDate): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            throw new RuntimeException("Database backups are not supported for the [{$driver}] driver.");
        }

        $tables = $this->tables($connection, $driver);
        $this->write($output, "-- Database backup generated at ".now()->toDateTimeString()."\n");
        $this->write($output, "-- Filename date selected: {$backupDate}\n\n");

        if ($driver === 'sqlite') {
            $this->write($output, "PRAGMA foreign_keys = OFF;\nBEGIN TRANSACTION;\n\n");
            foreach ($this->sqliteObjects($connection, 'view') as $view) {
                $this->write($output, 'DROP VIEW IF EXISTS '.$this->quoteIdentifier($view->name, $driver).";\n");
            }
            foreach ($this->sqliteObjects($connection, 'trigger') as $trigger) {
                $this->write($output, 'DROP TRIGGER IF EXISTS '.$this->quoteIdentifier($trigger->name, $driver).";\n");
            }
        } else {
            $this->write($output, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n");
        }

        foreach ($tables as $table) {
            $this->write($output, 'DROP TABLE IF EXISTS '.$this->quoteIdentifier($table, $driver).";\n");
        }
        $this->write($output, "\n");

        foreach ($tables as $table) {
            $createStatement = $this->createStatement($connection, $table, $driver);
            $this->write($output, $createStatement.";\n\n");
        }

        foreach ($tables as $table) {
            $columns = $this->columns($connection, $table, $driver);

            if ($columns === []) {
                continue;
            }

            $quotedColumns = implode(', ', array_map(
                fn (string $column): string => $this->quoteIdentifier($column, $driver),
                $columns
            ));

            foreach ($connection->cursor('SELECT * FROM '.$this->quoteIdentifier($table, $driver)) as $row) {
                $values = [];
                foreach ($columns as $column) {
                    $values[] = $this->quoteValue($row->{$column}, $connection, $driver);
                }

                $this->write(
                    $output,
                    'INSERT INTO '.$this->quoteIdentifier($table, $driver)
                    .' ('.$quotedColumns.') VALUES ('.implode(', ', $values).");\n"
                );
            }

            $this->write($output, "\n");
        }

        if ($driver === 'sqlite') {
            foreach (['view', 'index', 'trigger'] as $type) {
                foreach ($this->sqliteObjects($connection, $type) as $object) {
                    $this->write($output, rtrim($object->sql, ';').";\n\n");
                }
            }
        }

        if ($driver === 'sqlite') {
            $this->write($output, "COMMIT;\nPRAGMA foreign_keys = ON;\n");
        } else {
            $this->write($output, "SET FOREIGN_KEY_CHECKS = 1;\n");
        }
    }

    /**
     * @return list<object>
     */
    private function sqliteObjects(Connection $connection, string $type): array
    {
        return $connection->select(
            'SELECT name, sql FROM sqlite_master WHERE type = ? AND sql IS NOT NULL ORDER BY name',
            [$type]
        );
    }

    /**
     * @return list<string>
     */
    private function tables(Connection $connection, string $driver): array
    {
        if ($driver === 'sqlite') {
            return array_map(
                fn (object $row): string => $row->name,
                $connection->select(
                    "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' AND sql IS NOT NULL ORDER BY name"
                )
            );
        }

        $tables = [];
        foreach ($connection->select('SHOW FULL TABLES') as $row) {
            $values = array_values((array) $row);
            if (($values[1] ?? null) === 'BASE TABLE') {
                $tables[] = (string) $values[0];
            }
        }

        return $tables;
    }

    private function createStatement(Connection $connection, string $table, string $driver): string
    {
        if ($driver === 'sqlite') {
            $row = $connection->selectOne(
                "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?",
                [$table]
            );

            if (! $row?->sql) {
                throw new RuntimeException("Unable to read the schema for table [{$table}].");
            }

            return rtrim($row->sql, ';');
        }

        $row = $connection->selectOne('SHOW CREATE TABLE '.$this->quoteIdentifier($table, $driver));
        $statement = array_values((array) $row)[1] ?? null;

        if (! is_string($statement) || $statement === '') {
            throw new RuntimeException("Unable to read the schema for table [{$table}].");
        }

        return $statement;
    }

    /**
     * @return list<string>
     */
    private function columns(Connection $connection, string $table, string $driver): array
    {
        if ($driver === 'sqlite') {
            return array_map(
                fn (object $row): string => $row->name,
                $connection->select('PRAGMA table_info('.$this->quoteIdentifier($table, $driver).')')
            );
        }

        return array_map(
            fn (object $row): string => $row->Field,
            $connection->select('SHOW COLUMNS FROM '.$this->quoteIdentifier($table, $driver))
        );
    }

    private function quoteIdentifier(string $identifier, string $driver): string
    {
        if ($driver === 'mysql' || $driver === 'mariadb') {
            return '`'.str_replace('`', '``', $identifier).'`';
        }

        return '"'.str_replace('"', '""', $identifier).'"';
    }

    private function quoteValue(mixed $value, Connection $connection, string $driver): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_resource($value)) {
            $streamContents = stream_get_contents($value);
            if ($streamContents === false) {
                throw new RuntimeException('Unable to read a binary database value for the SQL backup.');
            }

            $value = $streamContents;
        }

        $value = (string) $value;
        if (str_contains($value, "\0") || preg_match('//u', $value) !== 1) {
            $hex = bin2hex($value);

            return $driver === 'sqlite' ? "X'{$hex}'" : '0x'.$hex;
        }

        $quoted = $connection->getPdo()->quote($value);
        if ($quoted === false) {
            throw new RuntimeException('Unable to encode a database value for the SQL backup.');
        }

        return $quoted;
    }

    /**
     * @param resource $output
     */
    private function write($output, string $contents): void
    {
        $written = fwrite($output, $contents);
        if ($written !== strlen($contents)) {
            throw new RuntimeException('Unable to write the complete database backup.');
        }
    }
}
