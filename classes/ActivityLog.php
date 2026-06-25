<?php

class ActivityLog {

    private PDO $db;

    private array $entityMapping = [
        'taak'      => ['idColumn' => 'idTask',     'table' => 'tasks',      'nameColumn' => 'titel'],
        'categorie' => ['idColumn' => 'idCategory', 'table' => 'categories', 'nameColumn' => 'naam'],
        'gebruiker' => ['idColumn' => 'idUser',     'table' => 'users',      'nameColumn' => 'naam'],
    ];

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create(string $actie, string $entiteit, int $entiteitId, int $userId) : void {
        $stmt = $this->db->prepare("INSERT INTO activity_log (actie, entiteit, entiteit_id, User_idUser) VALUES (?, ?, ?, ?)");
        $stmt->execute([$actie, $entiteit, $entiteitId, $userId]);
    }

    public function getAll(array $filter = []) : array {
        $query = "SELECT * FROM `activity_log` WHERE 1";
        $args = [];

        // @todo: add filter handling
        if(isset($filter['user']) && !empty($filter['user'])) {
            $query .= " AND `User_idUser` = ?";
            $args[] = (int) $filter['user'];
        }

        if(isset($filter['action']) && !empty($filter['action'])) {
            $query .= " AND `actie` = ?";
            $args[] = $filter['action'];
        }

        $orderBy = $filters['order'] ?? 'created_at';
        $query .= " ORDER BY `" . $orderBy . "` DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($args);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRelatedEntityName(array $entry) : string {

        if(!$entry['entiteit'])    { return ''; }
        if(!$entry['entiteit_id']) { return ''; }

        if(!isset($this->entityMapping[$entry['entiteit']])) {
            return "#" . $entry['entiteit_id'];
        }

        $args = $this->entityMapping[$entry['entiteit']];

        $stmt = $this->db->prepare("
            SELECT `" . $args['nameColumn'] . "` as `name`
            FROM `" . $args['table'] . "`
            WHERE `" . $args['idColumn'] . "` = ?");
        $stmt->execute([$entry['entiteit_id']]);
        $entity = $stmt->fetch();

        if(!$entity) { return "#" .$entry['entiteit_id']; }
        return '"' . $entity['name'] . '"';
    }
}