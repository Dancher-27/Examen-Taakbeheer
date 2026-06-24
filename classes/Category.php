<?php
class Category {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT idCategory, naam, kleurcode FROM categories ORDER BY naam ASC");
        return $stmt->fetchAll();
    }

    public function create(string $name, string $colorCode): array {
        $errors = [];
        if (empty($name)) {
            $errors['naam'] = 'Naam is verplicht.';
        } elseif ($this->nameExists($name)) {
            $errors['naam'] = 'Deze categorienaam bestaat al.';
        }
        if (empty($colorCode)) {
            $errors['kleurcode'] = 'Kleurcode is verplicht.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare("INSERT INTO categories (naam, kleurcode) VALUES (?, ?)");
        $stmt->execute([$name, $colorCode]);

        return ['success' => true];
    }

    private function nameExists(string $name): bool {
        $stmt = $this->db->prepare("SELECT idCategory FROM categories WHERE naam = ?");
        $stmt->execute([$name]);
        return (bool) $stmt->fetch();
    }
}
