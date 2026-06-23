<?php
class Task {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getForUser(int $userId, string $sort = 'deadline'): array {
        $orderBy = $sort === 'prioriteit'
            ? "FIELD(t.prioriteit, 'hoog', 'gemiddeld', 'laag')"
            : "t.deadline ASC";

        $stmt = $this->db->prepare("
            SELECT t.idTask, t.titel, t.prioriteit, t.status, t.deadline,
                   c.naam AS categorie_naam, c.kleurcode
            FROM tasks t
            LEFT JOIN categories c ON t.Category_idCategory = c.idCategory
            WHERE t.User_idUser = ?
            ORDER BY $orderBy
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
