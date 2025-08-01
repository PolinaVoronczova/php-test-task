<?php

namespace PhpTestTask;
class Search
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Поиск по полю body таблицы comments
    public function searchComments($searchTerm)
    {
        if (strlen($searchTerm) < 3) {
            throw new Exception('Введите минимум 3 символа для поиска.');
        }

        $stmt = $this->pdo->prepare("
            SELECT posts.title, comments.body
            FROM comments
            JOIN posts ON comments.post_id = posts.id
            WHERE comments.body LIKE :search
        ");

        $stmt->execute([':search' => "%$searchTerm%"]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}