SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. SYSTEM & CONFIGURATION TABLES
-- --------------------------------------------------------

-- Table: site_settings
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meta_key` varchar(100) NOT NULL,
  `meta_value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: packages
CREATE TABLE `packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `max_photos` int(11) NOT NULL,
  `max_maps` int(11) DEFAULT 1,
  `has_basic_info` tinyint(1) NOT NULL DEFAULT 1,
  `has_music` tinyint(1) DEFAULT 0,
  `has_countdown` tinyint(1) DEFAULT 0,
  `has_timeline` tinyint(1) DEFAULT 1,
  `has_rsvp_form` tinyint(1) DEFAULT 1,
  `has_video` tinyint(1) DEFAULT 0,
  `has_profile` tinyint(1) DEFAULT 0,
  `can_edit_slug` tinyint(1) DEFAULT 0,
  `has_gift_ideas` tinyint(1) DEFAULT 0,
  `has_analysis` tinyint(1) DEFAULT 0,
  `has_qr_code` tinyint(1) DEFAULT 0,
  `has_live_checkin` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: invitation_type
CREATE TABLE `invitation_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NOT NULL,
  `label_1` varchar(50) DEFAULT 'Host 1',
  `label_2` varchar(50) DEFAULT 'Host 2',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_name` (`type_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: events (Global event types)
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_title` varchar(100) NOT NULL,
  `icon_link` varchar(255) DEFAULT 'fa-star',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: location_types
CREATE TABLE `location_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NOT NULL,
  `icon_class` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: fonts
CREATE TABLE `fonts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `family` varchar(255) NOT NULL,
  `style` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: themes
CREATE TABLE `themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `theme_name` varchar(50) NOT NULL,
  `primary_color` varchar(7) NOT NULL,
  `secondary_color` varchar(7) NOT NULL,
  `bg_color` varchar(7) NOT NULL,
  `text_color` varchar(7) NOT NULL,
  `package_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 2. USER & INVITATION TABLES
-- --------------------------------------------------------

-- Table: users
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: invitations
CREATE TABLE `invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `music_file` varchar(255) DEFAULT NULL,
  `host_name_1` varchar(255) NOT NULL,
  `host_name_2` varchar(255) DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `unique_slug` varchar(255) NOT NULL,
  `status` enum('pending_payment','pending_approval','active','expired') DEFAULT 'pending_payment',
  `allow_comments` tinyint(1) DEFAULT 1,
  `guest_message` text DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_slug` (`unique_slug`),
  CONSTRAINT `invitations_fk_type` FOREIGN KEY (`type_id`) REFERENCES `invitation_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 3. INVITATION CONTENT & MEDIA
-- --------------------------------------------------------

-- Table: invitation_events (Specific events for an invitation)
CREATE TABLE `invitation_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invitation_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `event_time` varchar(20) NOT NULL,
  `event_description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `inv_events_fk_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inv_events_fk_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: locations
CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invitation_id` int(11) NOT NULL,
  `location_type_id` int(11) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `map_link` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `locations_fk_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `locations_fk_type` FOREIGN KEY (`location_type_id`) REFERENCES `location_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: media (Gallery)
CREATE TABLE `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invitation_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `media_type` enum('image','video') DEFAULT 'image',
  `is_main_photo` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `media_fk_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: stories
CREATE TABLE `stories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invitation_id` int(11) NOT NULL,
  `paragraph_order` int(11) DEFAULT 0,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `stories_fk_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: stories_media
CREATE TABLE `stories_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invitation_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `media_type` enum('image','video') DEFAULT 'image',
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `stories_media_fk_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: social_links
CREATE TABLE `social_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invitation_id` int(11) NOT NULL,
  `platform` enum('Instagram','Telegram','WhatsApp','Facebook','TikTok','YouTube','Other') NOT NULL,
  `social_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `social_fk_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: user_themes (Selected customization for an invitation)
CREATE TABLE `user_themes` (
  `invitation_id` int(11) NOT NULL,
  `theme_id` int(11) DEFAULT NULL,
  `font_id` int(11) DEFAULT NULL,
  `theme_style` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`invitation_id`),
  CONSTRAINT `user_themes_fk_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- 4. GUEST INTERACTION & PAYMENTS
-- --------------------------------------------------------

-- Table: rsvps
CREATE TABLE `rsvps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invitation_id` int(11) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_side` enum('Groom','Bride','General') DEFAULT 'General',
  `attendance_status` enum('Yes','No') DEFAULT 'Yes',
  `attendees_count` int(11) DEFAULT 1,
  `guest_qr_token` varchar(100) DEFAULT NULL,
  `have_qr` tinyint(1) DEFAULT 0,
  `attended` tinyint(1) DEFAULT 0,
  `checkin_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `guest_qr_token` (`guest_qr_token`),
  CONSTRAINT `rsvps_fk_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: guestbook
CREATE TABLE `guestbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invitation_id` int(11) NOT NULL,
  `rsvp_id` int(11) DEFAULT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_side` varchar(50) DEFAULT 'Guest',
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `guestbook_fk_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `guestbook_fk_rsvp` FOREIGN KEY (`rsvp_id`) REFERENCES `rsvps` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: gift_registry
CREATE TABLE `gift_registry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invitation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `gift_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `gift_fk_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: payments
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `invitation_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_action` enum('buy','upgrade') NOT NULL,
  `proof_img` varchar(255) NOT NULL,
  `payment_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `payments_fk_inv` FOREIGN KEY (`invitation_id`) REFERENCES `invitations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payments_fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;


based on this db schema make admin dashboard pages one by one
