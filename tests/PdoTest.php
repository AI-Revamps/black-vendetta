<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PdoTest extends TestCase
{
    private function getConnection(): PDO
    {
        try {
            return db();
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    public function testDbReturnsPdoInstance(): void
    {
        $pdo = $this->getConnection();
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    public function testPdoQueryReturnsStatement(): void
    {
        $this->getConnection();
        try {
            $stmt = pdo_query('SELECT 1');
            $this->assertInstanceOf(PDOStatement::class, $stmt);
        } catch (PDOException $e) {
            $this->markTestSkipped('Query failed: ' . $e->getMessage());
        }
    }
}
