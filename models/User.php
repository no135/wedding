<?php
require_once __DIR__ . '/Model.php';

class User extends Model {
    protected $table = 'users';

    public function getUserWithStats($user_id) {
        $query = "
            SELECT 
                u.*,
                COUNT(DISTINCT i.id) as total_invitations,
                COUNT(DISTINCT p.id) as total_payments,
                SUM(CASE WHEN p.payment_status = 'approved' THEN p.amount ELSE 0 END) as total_paid
            FROM users u
            LEFT JOIN invitations i ON u.id = i.user_id
            LEFT JOIN payments p ON u.id = p.user_id
            WHERE u.id = ?
            GROUP BY u.id
        ";
        return $this->db->fetch($query, [$user_id]);
    }

    public function getAllUsers($limit = null, $offset = 0) {
        $query = "
            SELECT 
                u.*,
                COUNT(DISTINCT i.id) as total_invitations,
                COUNT(DISTINCT p.id) as total_payments
            FROM users u
            LEFT JOIN invitations i ON u.id = i.user_id
            LEFT JOIN payments p ON u.id = p.user_id
            GROUP BY u.id
        ";

        if ($limit) {
            $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }

        return $this->db->fetchAll($query);
    }

    public function getTotalUsers() {
        $query = "SELECT COUNT(*) as total FROM users";
        $result = $this->db->fetch($query);
        return $result['total'];
    }

    public function updateRole($user_id, $role) {
        $query = "UPDATE users SET role = ? WHERE id = ?";
        return $this->db->execute($query, [$role, $user_id]);
    }
}
