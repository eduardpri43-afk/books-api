<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

/**
 * Читає книги з JSON-файлу та надає методи для їх пошуку.
 */
final class BookRepository
{
    /** @var array<int, array<string, mixed>> */
    private array $books;

    public function __construct(string $filePath)
    {
        if (!is_file($filePath)) {
            throw new RuntimeException("Файл з даними не знайдено: {$filePath}");
        }

        $data = json_decode((string) file_get_contents($filePath), true, 512, JSON_THROW_ON_ERROR);
        $this->books = is_array($data) ? $data : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAll(?string $genre = null, ?string $author = null): array
    {
        $result = array_filter($this->books, static function (array $book) use ($genre, $author): bool {
            if ($genre !== null && mb_strtolower($book['genre']) !== mb_strtolower($genre)) {
                return false;
            }
            if ($author !== null && mb_stripos($book['author'], $author) === false) {
                return false;
            }
            return true;
        });

        return array_values($result);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        foreach ($this->books as $book) {
            if ((int) $book['id'] === $id) {
                return $book;
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function genres(): array
    {
        $genres = array_unique(array_column($this->books, 'genre'));
        sort($genres);

        return array_values($genres);
    }
}
