
CREATE DATABASE IF NOT EXISTS anvica_nms;
USE anvica_nms;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','viewer') DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users(username,password_hash,role)
VALUES ('admin', '$2y$10$NyZoD4iy2P82LxY.7Li5NuJd2H5F0faRMqyx9fDf1r7WwcQHj6sgO', 'admin');

CREATE TABLE locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  location_id INT NULL,
  name VARCHAR(100) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  type ENUM('router','switch','firewall','server','cctv','ups','access_point','other') DEFAULT 'other',
  vendor VARCHAR(80),
  snmp_version ENUM('1','2c','3') DEFAULT '2c',
  snmp_community VARCHAR(100) DEFAULT 'public',
  snmp_port INT DEFAULT 161,
  ping_enabled TINYINT DEFAULT 1,
  snmp_enabled TINYINT DEFAULT 1,
  status ENUM('up','down','warning','unknown') DEFAULT 'unknown',
  last_seen DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(location_id) REFERENCES locations(id) ON DELETE SET NULL
);

CREATE TABLE metrics (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  device_id INT NOT NULL,
  metric_key VARCHAR(100) NOT NULL,
  metric_value DOUBLE NULL,
  unit VARCHAR(30),
  collected_at DATETIME NOT NULL,
  INDEX(device_id, metric_key, collected_at),
  FOREIGN KEY(device_id) REFERENCES devices(id) ON DELETE CASCADE
);

CREATE TABLE interfaces (
  id INT AUTO_INCREMENT PRIMARY KEY,
  device_id INT NOT NULL,
  if_index INT NOT NULL,
  if_name VARCHAR(100),
  if_speed BIGINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_interface(device_id, if_index),
  FOREIGN KEY(device_id) REFERENCES devices(id) ON DELETE CASCADE
);

CREATE TABLE interface_traffic (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  device_id INT NOT NULL,
  if_index INT NOT NULL,
  rx_bytes BIGINT DEFAULT 0,
  tx_bytes BIGINT DEFAULT 0,
  rx_bps DOUBLE DEFAULT 0,
  tx_bps DOUBLE DEFAULT 0,
  collected_at DATETIME NOT NULL,
  INDEX(device_id, if_index, collected_at),
  FOREIGN KEY(device_id) REFERENCES devices(id) ON DELETE CASCADE
);

CREATE TABLE alerts (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  device_id INT NULL,
  severity ENUM('critical','warning','info') DEFAULT 'info',
  title VARCHAR(200) NOT NULL,
  message TEXT,
  status ENUM('open','closed') DEFAULT 'open',
  created_at DATETIME NOT NULL,
  closed_at DATETIME NULL,
  FOREIGN KEY(device_id) REFERENCES devices(id) ON DELETE CASCADE
);

CREATE TABLE settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT
);

INSERT INTO settings(setting_key, setting_value) VALUES
('email_to','admin@example.com'),
('email_from','nms@example.com'),
('sms_webhook_url',''),
('whatsapp_webhook_url','');
