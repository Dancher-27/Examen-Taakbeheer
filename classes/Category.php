<?php
class Category {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT c.idCategory, c.naam, c.kleurcode, COUNT(t.idTask) AS taken_count
            FROM categories c
            LEFT JOIN tasks t ON t.Category_idCategory = c.idCategory
            GROUP BY c.idCategory
            ORDER BY c.naam ASC
        ");
        return $stmt->fetchAll();
    }

    public function create(string $name, string $colorCode): array {
        $errors = $this->validate($name, $colorCode);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare("INSERT INTO categories (naam, kleurcode) VALUES (?, ?)");
        $stmt->execute([$name, $colorCode]);

        return ['success' => true];
    }

    public function read(int $id): array {
        $stmt = $this->db->prepare("
            SELECT *
            FROM `categories`
            WHERE `idCategory` = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update(int $id, string $name, string $colorCode): array {
        $errors = $this->validate($name, $colorCode, $id);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare("
            UPDATE `categories` SET
                naam = ?,
                kleurcode = ?
            WHERE `idCategory` = ?
        ");
        $stmt->execute([$name, $colorCode, $id]);

        return ['success' => true];
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM `categories` WHERE `idCategory` = ?");
        return $stmt->execute([$id]);
    }

    private function validate(string $name, string $colorCode, ?int $excludeId = null): array {
        $errors = [];

        if (empty($name)) {
            $errors['naam'] = 'Naam is verplicht.';
        } elseif ($this->nameExists($name, $excludeId)) {
            $errors['naam'] = 'Deze categorienaam bestaat al.';
        }

        if (empty($colorCode)) {
            $errors['kleurcode'] = 'Kleurcode is verplicht.';
        }

        return $errors;
    }

    private function nameExists(string $name, ?int $excludeId = null): bool {
        $sql = "SELECT idCategory FROM categories WHERE naam = ?";
        $params = [$name];

        if ($excludeId !== null) {
            $sql .= " AND idCategory != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }
}
