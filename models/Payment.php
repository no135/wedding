<?php
require_once __DIR__ . '/Model.php';

class Payment extends Model {
    protected $table = 'payments';

    public function getPaymentWithDetails($payment_id) {
        $query = "
            SELECT 
                p.*,
                u.fullname,
                u.email,
                i.host_name_1,
                pkg.package_name
            FROM payments p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN invitations i ON p.invitation_id = i.id
            LEFT JOIN packages pkg ON i.package_id = pkg.id
            WHERE p.id = ?
        ";
        return $this->db->fetch($query, [$payment_id]);
    }

    public function getPaymentsByStatus($status, $limit = null, $offset = 0) {
        $query = "
            SELECT 
                p.*,
                u.fullname,
                u.email,
                i.host_name_1,
                pkg.package_name
            FROM payments p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN invitations i ON p.invitation_id = i.id
            LEFT JOIN packages pkg ON i.package_id = pkg.id
            WHERE p.payment_status = ?
            ORDER BY p.created_at DESC
        ";

        $params = [$status];

        if ($limit) {
            $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }

        return $this->db->fetchAll($query, $params);
    }

    public function getUserPayments($user_id) {
        $query = "
            SELECT 
                p.*,
                i.host_name_1,
                pkg.package_name
            FROM payments p
            LEFT JOIN invitations i ON p.invitation_id = i.id
            LEFT JOIN packages pkg ON i.package_id = pkg.id
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
        ";
        return $this->db->fetchAll($query, [$user_id]);
    }

    public function getTotalRevenue() {
        $query = "
            SELECT 
                SUM(CASE WHEN payment_status = 'approved' THEN amount ELSE 0 END) as approved,
                SUM(CASE WHEN payment_status = 'pending' THEN amount ELSE 0 END) as pending,
                SUM(CASE WHEN payment_status = 'rejected' THEN amount ELSE 0 END) as rejected,
                COUNT(*) as total_transactions
            FROM payments
        ";
        return $this->db->fetch($query);
    }

    public function getPaymentStats() {
        $query = "
            SELECT 
                payment_status,
                COUNT(*) as count,
                SUM(amount) as total
            FROM payments
            GROUP BY payment_status
        ";
        return $this->db->fetchAll($query);
    }

    public function approvePayment($payment_id, $admin_note = '') {
        $query = "UPDATE payments SET payment_status = 'approved', admin_note = ? WHERE id = ?";
        return $this->db->execute($query, [$admin_note, $payment_id]);
    }

    public function rejectPayment($payment_id, $admin_note = '') {
        $query = "UPDATE payments SET payment_status = 'rejected', admin_note = ? WHERE id = ?";
        return $this->db->execute($query, [$admin_note, $payment_id]);
    }

    public function getPendingPayments() {
        $query = "SELECT COUNT(*) as total FROM payments WHERE payment_status = 'pending'";
        $result = $this->db->fetch($query);
        return $result['total'];
    }
}
