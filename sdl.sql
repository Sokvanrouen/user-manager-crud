USE crud_db;

CREATE TABLE IF NOT EXISTS users (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    role  ENUM('Admin', 'Editor', 'Viewer') DEFAULT 'Viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Optional: seed with sample data
INSERT INTO users (name, email, role) VALUES
  ('Alice Santos',  'alice@example.com',  'Admin'),
  ('Bob Cruz',      'bob@example.com',    'Editor'),
  ('Carla Reyes',   'carla@example.com',  'Viewer');