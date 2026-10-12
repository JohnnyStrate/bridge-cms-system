<?php
declare(strict_types=1);

/**
 * Al SQL der rører tabellen `publish_files`.
 *
 * Tabellen er CMS'ets huskeliste over, hvad der ligger på webserveren:
 * filens sti (relativt til eksportmappen) og et sha1-fingeraftryk af
 * indholdet. FtpPublisher bruger den til kun at uploade det, der er
 * ændret, og til kun at slette filer, vi selv har lagt derude.
 *
 * SQL'en er bevidst standard (REPLACE INTO, simple SELECT/DELETE), så den
 * virker både på MariaDB og i en SQLite-test.
 */
final class PublishFilesRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<string, string> sti => sha1
     */
    public function all(): array
    {
        return $this->run(function (): array {
            $rows   = $this->pdo->query('SELECT `path`, `hash` FROM publish_files')->fetchAll(PDO::FETCH_NUM);
            $result = [];

            // Stien bliver nøgle. Den castes til streng, så en sti, der
            // ligner et tal, ikke bliver til et heltal.
            foreach ($rows as [$path, $hash]) {
                $result[(string) $path] = (string) $hash;
            }

            return $result;
        });
    }

    public function save(string $path, string $hash): void
    {
        $this->run(function () use ($path, $hash): void {
            $stmt = $this->pdo->prepare(
                'REPLACE INTO publish_files (`path`, `hash`) VALUES (:path, :hash)'
            );
            $stmt->execute(['path' => $path, 'hash' => $hash]);
        });
    }

    public function delete(string $path): void
    {
        $this->run(function () use ($path): void {
            $stmt = $this->pdo->prepare('DELETE FROM publish_files WHERE `path` = :path');
            $stmt->execute(['path' => $path]);
        });
    }

    /** Glemmer alt. Næste udgivelse uploader derfor alle filer. */
    public function clear(): void
    {
        $this->run(function (): void {
            $this->pdo->exec('DELETE FROM publish_files');
        });
    }

    /**
     * Mangler tabellen (migrationen er ikke kørt), får brugeren en besked,
     * der siger, hvad der skal gøres — ikke en rå databasefejl.
     *
     * @template T
     * @param callable(): T $work
     * @return T
     */
    private function run(callable $work): mixed
    {
        try {
            return $work();
        } catch (PDOException $e) {
            // 42S02 er MySQL/MariaDBs kode for "tabellen findes ikke".
            if ($e->getCode() === '42S02') {
                throw new RuntimeException(
                    'Tabellen publish_files mangler. Kør database/migrations/'
                    . '2026-10-06_publish_files.sql i phpMyAdmin og prøv igen.',
                    0,
                    $e
                );
            }

            throw $e;
        }
    }
}
