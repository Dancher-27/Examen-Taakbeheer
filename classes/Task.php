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

    public function getAllForAdmin(array $filters = []): array {
        $sql = "
            SELECT t.idTask, t.titel, t.prioriteit, t.status, t.deadline,
                   c.naam AS categorie_naam, c.kleurcode,
                   u.naam AS gebruiker_naam
            FROM tasks t
            LEFT JOIN categories c ON t.Category_idCategory = c.idCategory
            LEFT JOIN users u ON t.User_idUser = u.idUser
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['user'])) {
            $sql .= " AND t.User_idUser = ?";
            $params[] = $filters['user'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['prioriteit'])) {
            $sql .= " AND t.prioriteit = ?";
            $params[] = $filters['prioriteit'];
        }
        if (!empty($filters['categorie'])) {
            $sql .= " AND t.Category_idCategory = ?";
            $params[] = $filters['categorie'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND t.titel LIKE ?";
            $params[] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY t.deadline ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCategories(): array {
        $stmt = $this->db->query("SELECT idCategory, naam FROM categories ORDER BY naam ASC");
        return $stmt->fetchAll();
    }
}
