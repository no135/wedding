<?php
require_once __DIR__ . '/Model.php';

class RSVP extends Model {
    protected $table = 'rsvps';

    public function getInvitationRSVPs($invitation_id) {
        $query = "
            SELECT * FROM rsvps
            WHERE invitation_id = ?
            ORDER BY created_at DESC
        ";
        return $this->db->fetchAll($query, [$invitation_id]);
    }

    public function getInvitationGuestStats($invitation_id) {
        $query = "
            SELECT 
                COUNT(*) as total_guests,
                COUNT(CASE WHEN attendance_status = 'Yes' THEN 1 END) as confirmed,
                COUNT(CASE WHEN attendance_status = 'No' THEN 1 END) as declined,
                COUNT(CASE WHEN attended = 1 THEN 1 END) as checked_in,
                SUM(attendees_count) as total_attendees
            FROM rsvps
            WHERE invitation_id = ?
        ";
        return $this->db->fetch($query, [$invitation_id]);
    }

    public function getRSVPByQRToken($qr_token) {
        $query = "SELECT * FROM rsvps WHERE guest_qr_token = ?";
        return $this->db->fetch($query, [$qr_token]);
    }

    public function checkInGuest($rsvp_id) {
        $query = "UPDATE rsvps SET attended = 1, checkin_time = NOW() WHERE id = ?";
        return $this->db->execute($query, [$rsvp_id]);
    }

    public function getUnattendedGuests($invitation_id) {
        $query = "
            SELECT * FROM rsvps
            WHERE invitation_id = ? AND attended = 0
            ORDER BY created_at DESC
        ";
        return $this->db->fetchAll($query, [$invitation_id]);
    }

    public function getAttendedGuests($invitation_id) {
        $query = "
            SELECT * FROM rsvps
            WHERE invitation_id = ? AND attended = 1
            ORDER BY checkin_time DESC
        ";
        return $this->db->fetchAll($query, [$invitation_id]);
    }

    public function generateQRToken() {
        do {
            $token = strtoupper(bin2hex(random_bytes(6)));
            $query = "SELECT id FROM rsvps WHERE guest_qr_token = ?";
            $result = $this->db->fetch($query, [$token]);
        } while ($result);

        return $token;
    }

    public function bulkGenerateQRTokens($invitation_id) {
        $query = "
            SELECT id FROM rsvps
            WHERE invitation_id = ? AND guest_qr_token IS NULL
        ";
        $guests = $this->db->fetchAll($query, [$invitation_id]);

        foreach ($guests as $guest) {
            $token = $this->generateQRToken();
            $update_query = "UPDATE rsvps SET guest_qr_token = ?, have_qr = 1 WHERE id = ?";
            $this->db->execute($update_query, [$token, $guest['id']]);
        }

        return count($guests);
    }

    public function getGuestsByAttendanceStatus($invitation_id, $status) {
        $query = "
            SELECT * FROM rsvps
            WHERE invitation_id = ? AND attendance_status = ?
            ORDER BY created_at DESC
        ";
        return $this->db->fetchAll($query, [$invitation_id, $status]);
    }
}
