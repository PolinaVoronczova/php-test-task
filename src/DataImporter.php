<?php

namespace PhpTestTask;

class DataImporter
{
    private $pdo;
    private $postsCount = 0;
    private $commentsCount = 0;

    public function __construct()
    {
        define('POSTS_URL', 'https://jsonplaceholder.typicode.com/posts');
        define('COMMENTS_URL', 'https://jsonplaceholder.typicode.com/comments');
        define('CONFIG_FILE', __DIR__ . '/database.ini');

        $this->pdo = $this->getPDOConnection();
        $this->prepareStatements();
    }

    // Подготавливаем выражения
    private function prepareStatements()
    {
        $this->checkPostStmt = $this->pdo->prepare("SELECT 1 FROM posts WHERE id = :id");
        $this->checkCommentStmt = $this->pdo->prepare("SELECT 1 FROM comments WHERE id = :id");
        
        $this->stmtPost = $this->pdo->prepare("
            INSERT INTO posts (id, user_id, title, body, created_at)
            VALUES (:id, :userId, :title, :body, NOW())
        ");
        
        $this->stmtComment = $this->pdo->prepare("
        INSERT INTO comments (id, post_id, name, email, body, created_at)
        VALUES (:id, :post_id, :name, :email, :body, NOW())
        ");
    }

    // Получаем данные по указанной ссылке
    private function getData($url)
    {
        $response = file_get_contents($url);
        if ($response === false) {
            throw new \Exception("Данные по ссылке {$url} не получены.");
        }
        
        $data = json_decode($response, true);

        return $data;
    }

    // Загружаем записи в бд
    private function importPosts($posts)
    {
        foreach ($posts as $post) {
            try {
                $this->checkPostStmt->execute([$post['id']]);
                if ($this->checkPostStmt->fetchColumn()) {
                    echo "Пост с ID: {$post['id']} уже существует, пропускаем.\n";
                    continue;
                }

                $result = $this->stmtPost->execute([
                    ':id' => $post['id'],
                    ':userId'    => $post['userId'],
                    ':title'   => $post['title'],
                    ':body'    => $post['body']
                ]);

                $this->postsCount++;
                echo "Добавлен пост с ID: {$post['id']}\n";
            } catch (PDOException $e) {
                die("Ошибка при работе с БД: " . $e->getMessage());
            }
        }
    }

    // Загружаем комментарии в бд
    private function importComments($comments)
    {
        foreach ($comments as $comment) {
            try {
                $this->checkPostStmt->execute([$comment['postId']]);
                if (!$this->checkPostStmt->fetchColumn()) {
                    echo "Пост с id {$comment['postId']} не найден. Комментарий {$comment['id']} не импортирован.";
                    continue;
                }

                $this->checkCommentStmt->execute([$comment['id']]);
                if ($this->checkCommentStmt->fetchColumn()) {
                    echo "Комментарий с ID: {$comment['id']} уже существует, пропускаем.\n";
                    continue;
                }

                $result = $this->stmtComment->execute([
                    ':id' => $comment['id'],
                    ':post_id' => $comment['postId'],
                    ':name' => $comment['name'],
                    ':email' => $comment['email'],
                    ':body' => $comment['body']
                ]);

                $this->commentsCount++;
                echo "Добавлен комментарий с ID: {$comment['id']}.\n";
            } catch (PDOException $e) {
                die("Ошибка при работе с БД: " . $e->getMessage());
            }
        }
    }

    public function runImport()
    {
        try {
            $posts = $this->getData(POSTS_URL);
            $comments = $this->getData(COMMENTS_URL);

            $this->importPosts($posts);
            $this->importComments($comments);

            echo "Загружено {$this->postsCount} записей и {$this->commentsCount} комментариев.\n";
        } catch (\Exception $e) {
            die("При импорте произошла ошибка: " . $e->getMessage());
        }
    }

    // Подключаемся к бд, параметры для подключения получаем из файла /src/database.ini
    private function getPDOConnection()
    {
        try {
            $params = parse_ini_file(CONFIG_FILE);
        } catch (PDOException $e) {
            throw new \Exception("Файл {CONFIG_FILE} не найден.");
        }
        

        if ($params === false) {
            throw new \Exception("Невозможно причитать файл с конфигурацией базы данных.");
        }

        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $params['host'],
            $params['port'],
            $params['database'],
            $params['user'],
            $params['password']
        );

        try {
            $pdo = new \PDO($conStr);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new \Exception("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }
}

