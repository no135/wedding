<?php
require_once __DIR__ . '/Model.php';

class Invitation extends Model {
    protected $table = 'invitations';

    public function getInvitationWithDetails($invitation_id) {
        $query = "
            SELECT 
                i.*,
                u.fullname as user_fullname,
                u.email as user_email,
                p.package_name,
                p.price,
                it.type_name,
                it.label_1,
                it.label_2,
                COUNT(DISTINCT r.id) as total_guests,
                COUNT(DISTINCT CASE WHEN r.attendance_status = 'Yes' THEN r.id END) as confirmed_guests,
                COUNT(DISTINCT gb.id) as total_messages,
                COUNT(DISTINCT m.id) as total_media
            FROM invitations i
            LEFT JOIN users u ON i.user_id = u.id
            LEFT JOIN packages p ON i.package_id = p.id
            LEFT JOIN invitation_type it ON i.type_id = it.id
            LEFT JOIN rsvps r ON i.id = r.invitation_id
            LEFT JOIN guestbook gb ON i.id = gb.invitation_id
            LEFT JOIN media m ON i.id = m.invitation_id
            WHERE i.id = ?
            GROUP BY i.id
        ";
        return $this->db->fetch($query, [$invitation_id]);
    }

    public function getUserInvitations($user_id, $status = null) {
        $query = "
            SELECT 
                i.*,
                p.package_name,
                COUNT(DISTINCT r.id) as total_guests,
                COUNT(DISTINCT CASE WHEN r.attended = 1 THEN r.id END) as attended_guests
            FROM invitations i
            LEFT JOIN packages p ON i.package_id = p.id
            LEFT JOIN rsvps r ON i.id = r.invitation_id
            WHERE i.user_id = ?
        ";

        $params = [$user_id];

        if ($status) {
            $query .= " AND i.status = ?";
            $params[] = $status;
        }

        $query .= " GROUP BY i.id ORDER BY i.created_at DESC";

        return $this->db->fetchAll($query, $params);
    }

    public function getAllInvitations($limit = null, $offset = 0) {
        $query = "
            SELECT 
                i.*,
                u.fullname,
                p.package_name,
                COUNT(DISTINCT r.id) as total_guests
            FROM invitations i
            LEFT JOIN users u ON i.user_id = u.id
            LEFT JOIN packages p ON i.package_id = p.id
            LEFT JOIN rsvps r ON i.id = r.invitation_id
            GROUP BY i.id
            ORDER BY i.created_at DESC
        ";

        if ($limit) {
            $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }

        return $this->db->fetchAll($query);
    }

    public function getInvitationBySlug($slug) {
        $query = "SELECT * FROM invitations WHERE unique_slug = ?";
        return $this->db->fetch($query, [$slug]);
    }

    public function getTotalInvitations() {
        $query = "SELECT COUNT(*) as total FROM invitations";
        $result = $this->db->fetch($query);
        return $result['total'];
    }

    public function getInvitationsByStatus($status) {
        $query = "SELECT COUNT(*) as total FROM invitations WHERE status = ?";
        $result = $this->db->fetch($query, [$status]);
        return $result['total'];
    }

    public function incrementViewCount($invitation_id) {
        $query = "UPDATE invitations SET view_count = view_count + 1 WHERE id = ?";
        return $this->db->execute($query, [$invitation_id]);
    }

    public function updateStatus($invitation_id, $status) {
        $query = "UPDATE invitations SET status = ? WHERE id = ?";
        return $this->db->execute($query, [$status, $invitation_id]);
    }
}
