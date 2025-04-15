<?php
use PHPUnit\Framework\TestCase;

class ReplyTest extends TestCase
{
    public function testReplyCreation()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=forum', 'root', '');
        $stmt = $pdo->prepare("INSERT INTO replies (topic_id, content, user_id) VALUES (?, ?, ?)");
        $result = $stmt->execute([1, 'Тестовый ответ', 1]);

        $this->assertTrue($result);
    }

    public function testReplyFetch()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=forum', 'root', '');
        $stmt = $pdo->prepare("SELECT * FROM replies WHERE topic_id = ?");
        $stmt->execute([1]);
        $replies = $stmt->fetchAll();

        $this->assertIsArray($replies);
    }

    public function testUserExists()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=forum', 'root', '');
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([1]);
        $user = $stmt->fetch();

        $this->assertNotEmpty($user);
    }
}