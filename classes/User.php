<?php
class User {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function register(string $name, string $email, string $password): array {
        if (empty($name) || empty($email) || empty($password)) {
            return ['success' => false, 'errors' => $this->validateFields($name, $email, $password)];
        }

        $errors = $this->validateFields($name, $email, $password);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        if ($this->emailExists($email)) {
            return ['success' => false, 'errors' => ['email' => 'Dit e-mailadres is al in gebruik.']];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (naam, email, wachtwoord_hash, rol) VALUES (?, ?, ?, 'gebruiker')");
        $stmt->execute([$name, $email, $hash]);

        return ['success' => true];
    }

    public function login(string $email, string $password): array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['wachtwoord_hash'])) {
            return ['success' => false, 'error' => 'Ongeldige inloggegevens.'];
        }

        session_start();
        $_SESSION['user_id'] = $user['idUser'];
        $_SESSION['user_name'] = $user['naam'];
        $_SESSION['user_role'] = $user['rol'];

        return ['success' => true, 'role' => $user['rol']];
    }

    public function getAll(string $search = '', string $roleFilter = ''): array {
        $sql = "SELECT idUser, naam, email, rol FROM users WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (naam LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($roleFilter !== '') {
            $sql .= " AND rol = ?";
            $params[] = $roleFilter;
        }

        $sql .= " ORDER BY naam ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function emailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT idUser FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    private function validateFields(string $name, string $email, string $password): array {
        $errors = [];
        if (empty($name)) $errors['name'] = 'Naam is verplicht.';
        if (empty($email)) {
            $errors['email'] = 'E-mailadres is verplicht.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Voer een geldig e-mailadres in.';
        }
        if (empty($password)) {
            $errors['password'] = 'Wachtwoord is verplicht.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Wachtwoord moet minimaal 8 tekens bevatten.';
        }
        return $errors;
    }
}
