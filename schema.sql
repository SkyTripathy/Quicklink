CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `short_code` varchar(20) NOT NULL,
  `original_url` text NOT NULL,
  `utm_source` varchar(100) DEFAULT NULL,
  `utm_medium` varchar(100) DEFAULT NULL,
  `utm_campaign` varchar(100) DEFAULT NULL,
  `utm_term` varchar(100) DEFAULT NULL,
  `utm_content` varchar(100) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_code` (`short_code`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `clicks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `country` varchar(100) DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL, /* Desktop, Mobile, Tablet */
  `referer` text,
  `clicked_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`link_id`) REFERENCES `links`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert a default admin user with password 'admin123' (password_hash generated via password_hash('admin123', PASSWORD_DEFAULT))
INSERT INTO `users` (`username`, `password_hash`) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi') ON DUPLICATE KEY UPDATE username='admin';
