<?php
class Task {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getStatusCounts(): array {
        $stmt = $this->db->query("SELECT status, COUNT(*) AS aantal FROM tasks GROUP BY status");
        $counts = ['open' => 0, 'in_progress' => 0, 'done' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['aantal'];
        }
        return $counts;
    }

    public function getOverdueCount(): int {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS aantal FROM tasks
            WHERE deadline < CURDATE() AND status != 'done'
        ");
        return (int) $stmt->fetch()['aantal'];
    }

    public function getForUser(int $userId, array $filters = [], string $sort = 'deadline'): array {
        $orderBy = $sort === 'prioriteit'
            ? "FIELD(t.prioriteit, 'hoog', 'gemiddeld', 'laag')"
            : "t.deadline ASC";

        $sql = "
            SELECT t.idTask, t.titel, t.prioriteit, t.status, t.deadline,
                   c.naam AS categorie_naam, c.kleurcode
            FROM tasks t
            INNER JOIN task_user tu ON tu.Task_idTask = t.idTask
            LEFT JOIN categories c ON t.Category_idCategory = c.idCategory
            WHERE tu.User_idUser = ?
        ";
        $params = [$userId];

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

        $sql .= " ORDER BY $orderBy";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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

    public function createForSelf(array $data, int $userId): array {
        $errors = [];
        if (empty($data['titel'])) {
            $errors['titel'] = 'Titel is verplicht.';
        }
        if (empty($data['prioriteit']) || !in_array($data['prioriteit'], ['laag', 'gemiddeld', 'hoog'], true)) {
            $errors['prioriteit'] = 'Kies een geldige prioriteit.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare("
            INSERT INTO tasks (titel, beschrijving, prioriteit, status, deadline, Category_idCategory, User_idUser)
            VALUES (?, ?, ?, 'open', ?, ?, ?)
        ");
        $stmt->execute([
            $data['titel'],
            $data['beschrijving'] !== '' ? $data['beschrijving'] : null,
            $data['prioriteit'],
            $data['deadline'] !== '' ? $data['deadline'] : null,
            $data['categorie'] !== '' ? $data['categorie'] : null,
            $userId,
        ]);
        $taskId = (int) $this->db->lastInsertId();

        $stmt = $this->db->prepare("INSERT INTO task_user (Task_idTask, User_idUser) VALUES (?, ?)");
        $stmt->execute([$taskId, $userId]);

        $this->logActivity($userId, 'aangemaakt', 'taak', $taskId);

        return ['success' => true];
    }

    public function delete(int $taskId, int $userId): array {
        $this->db->prepare("DELETE FROM task_user WHERE Task_idTask = ?")->execute([$taskId]);
        $this->db->prepare("DELETE FROM task_progress WHERE Task_idTask = ?")->execute([$taskId]);
        $this->db->prepare("DELETE FROM tasks WHERE idTask = ?")->execute([$taskId]);

        $this->logActivity($userId, 'verwijderd', 'taak', $taskId);

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

    public function getById(int $taskId): ?array {
        $stmt = $this->db->prepare("
            SELECT t.idTask, t.titel, t.beschrijving, t.prioriteit, t.status, t.deadline,
                   t.Category_idCategory, c.naam AS categorie_naam, c.kleurcode
            FROM tasks t
            LEFT JOIN categories c ON t.Category_idCategory = c.idCategory
            WHERE t.idTask = ?
        ");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch();
        return $task ?: null;
    }

    public function update(int $taskId, array $data, int $userId, bool $isAdmin): array {
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
        if ($isAdmin && empty($data['gebruikers'])) {
            $errors['gebruikers'] = 'Selecteer minstens één gebruiker.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare("
            UPDATE tasks
            SET titel = ?, beschrijving = ?, prioriteit = ?, status = ?, deadline = ?, Category_idCategory = ?
            WHERE idTask = ?
        ");
        $stmt->execute([
            $data['titel'],
            $data['beschrijving'] !== '' ? $data['beschrijving'] : null,
            $data['prioriteit'],
            $data['status'],
            $data['deadline'] !== '' ? $data['deadline'] : null,
            $data['categorie'] !== '' ? $data['categorie'] : null,
            $taskId,
        ]);

        if ($isAdmin) {
            $this->db->prepare("DELETE FROM task_user WHERE Task_idTask = ?")->execute([$taskId]);
            $assignStmt = $this->db->prepare("INSERT INTO task_user (Task_idTask, User_idUser) VALUES (?, ?)");
            foreach ($data['gebruikers'] as $assignedId) {
                $assignStmt->execute([$taskId, (int) $assignedId]);
            }
        }

        $this->logActivity($userId, 'bewerkt', 'taak', $taskId);

        return ['success' => true];
    }

    public function getAssignedUsers(int $taskId): array {
        $stmt = $this->db->prepare("
            SELECT u.idUser, u.naam
            FROM task_user tu
            INNER JOIN users u ON tu.User_idUser = u.idUser
            WHERE tu.Task_idTask = ?
            ORDER BY u.naam ASC
        ");
        $stmt->execute([$taskId]);
        return $stmt->fetchAll();
    }

    public function isAssignedToUser(int $taskId, int $userId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM task_user WHERE Task_idTask = ? AND User_idUser = ?");
        $stmt->execute([$taskId, $userId]);
        return (bool) $stmt->fetch();
    }

    public function getProgressUpdates(int $taskId): array {
        $stmt = $this->db->prepare("
            SELECT tp.idTaskProgress, tp.beschrijving, tp.created_at, u.naam AS gebruiker_naam
            FROM task_progress tp
            INNER JOIN users u ON tp.User_idUser = u.idUser
            WHERE tp.Task_idTask = ?
            ORDER BY tp.created_at ASC
        ");
        $stmt->execute([$taskId]);
        return $stmt->fetchAll();
    }

    public function addProgressUpdate(int $taskId, int $userId, string $description): array {
        if (trim($description) === '') {
            return ['success' => false, 'error' => 'Beschrijving is verplicht.'];
        }

        $stmt = $this->db->prepare("INSERT INTO task_progress (beschrijving, Task_idTask, User_idUser) VALUES (?, ?, ?)");
        $stmt->execute([trim($description), $taskId, $userId]);

        return ['success' => true];
    }

    public static function getUrgencyByDeadline($deadline): string {

        // Task without deadline gets no urgency indicator
        if(empty($deadline)) {
            return '';
        }

        $dateDeadline = new DateTime($deadline);
        $dateToday = new DateTime('today');
        $dateDiff = $dateDeadline->diff($dateToday);

        // If deadline has passed or is today, return high urgency value
        if($dateDiff->invert == 0 || $dateDiff->days == 0) {
            return 'urgency-high';
        }

        // If deadline is in less than three days, return medium urgency value
        if($dateDiff->days < 3) {
            return 'urgency-medium';
        }

        // In all other cases, return low urgency value
        return 'urgency-low';
    }
}
