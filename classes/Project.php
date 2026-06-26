<?php
class Project {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT p.idProject, p.naam, p.beschrijving, COUNT(t.idTask) AS taken_count
            FROM projects p
            LEFT JOIN tasks t ON t.Project_idProject = p.idProject
            GROUP BY p.idProject
            ORDER BY p.naam ASC
        ");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT idProject, naam, beschrijving FROM projects WHERE idProject = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        return $project ?: null;
    }

    public function create(string $name, string $description): array {
        $errors = $this->validate($name);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare("INSERT INTO projects (naam, beschrijving) VALUES (?, ?)");
        $stmt->execute([$name, $description !== '' ? $description : null]);

        return ['success' => true];
    }

    public function update(int $id, string $name, string $description): array {
        $errors = $this->validate($name, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare("UPDATE projects SET naam = ?, beschrijving = ? WHERE idProject = ?");
        $stmt->execute([$name, $description !== '' ? $description : null, $id]);

        return ['success' => true];
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM projects WHERE idProject = ?");
        return $stmt->execute([$id]);
    }

    private function validate(string $name, ?int $excludeId = null): array {
        $errors = [];
        if (empty($name)) {
            $errors['naam'] = 'Naam is verplicht.';
        } elseif ($this->nameExists($name, $excludeId)) {
            $errors['naam'] = 'Deze projectnaam bestaat al.';
        }
        return $errors;
    }

    private function nameExists(string $name, ?int $excludeId = null): bool {
        $sql = "SELECT idProject FROM projects WHERE naam = ?";
        $params = [$name];

        if ($excludeId !== null) {
            $sql .= " AND idProject != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }
}
