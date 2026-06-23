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
            INNER JOIN task_user tu ON tu.Task_idTask = t.idTask
            LEFT JOIN categories c ON t.Category_idCategory = c.idCategory
            WHERE tu.User_idUser = ?
            ORDER BY $orderBy
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create(array $data, int $creatorId): array {
        $errors = [];
        if (empty($data['titel'])) {
            $errors['titel'] = 'Titel is verplicht.';
        }
        if (empty($data['prioriteit']) || !in_array($data['prioriteit'], ['laag', 'gemiddeld', 'hoog'], true)) {
            $errors['prioriteit'] = 'Kies een geldige prioriteit.';
        }
        if (empty($data['status']) || !in_array($data['status'], ['open', 'in_progress', 'done'], true)) {
            $errors['status'] = 'Kies een geldige status.';
        }
        if (empty($data['gebruikers'])) {
            $errors['gebruikers'] = 'Selecteer minstens één gebruiker.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare("
            INSERT INTO tasks (titel, beschrijving, prioriteit, status, deadline, Category_idCategory, User_idUser)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['titel'],
            $data['beschrijving'] !== '' ? $data['beschrijving'] : null,
            $data['prioriteit'],
            $data['status'],
            $data['deadline'] !== '' ? $data['deadline'] : null,
            $data['categorie'] !== '' ? $data['categorie'] : null,
            $creatorId,
        ]);
        $taskId = (int) $this->db->lastInsertId();

        $assignStmt = $this->db->prepare("INSERT INTO task_user (Task_idTask, User_idUser) VALUES (?, ?)");
        foreach ($data['gebruikers'] as $userId) {
            $assignStmt->execute([$taskId, (int) $userId]);
        }

        $this->logActivity($creatorId, 'aangemaakt', 'taak', $taskId);

        return ['success' => true];
    }

    private function logActivity(int $userId, string $actie, string $entiteit, int $entiteitId): void {
        $stmt = $this->db->prepare("INSERT INTO activity_log (actie, entiteit, entiteit_id, User_idUser) VALUES (?, ?, ?, ?)");
        $stmt->execute([$actie, $entiteit, $entiteitId, $userId]);
    }

    public function getAllForAdmin(array $filters = []): array {
        $sql = "
            SELECT t.idTask, t.titel, t.prioriteit, t.status, t.deadline,
                   c.naam AS categorie_naam, c.kleurcode,
                   GROUP_CONCAT(u.naam SEPARATOR ', ') AS gebruiker_naam
            FROM tasks t
            LEFT JOIN categories c ON t.Category_idCategory = c.idCategory
            LEFT JOIN task_user tu ON tu.Task_idTask = t.idTask
            LEFT JOIN users u ON tu.User_idUser = u.idUser
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['user'])) {
            $sql .= " AND t.idTask IN (SELECT Task_idTask FROM task_user WHERE User_idUser = ?)";
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

        $sql .= " GROUP BY t.idTask ORDER BY t.deadline ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCategories(): array {
        $stmt = $this->db->query("SELECT idCategory, naam FROM categories ORDER BY naam ASC");
        return $stmt->fetchAll();
    }
}
