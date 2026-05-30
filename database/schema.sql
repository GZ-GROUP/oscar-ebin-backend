\c postgres

DROP DATABASE IF EXISTS oscardb;

CREATE DATABASE oscardb;

\c oscardb

-- Necesario para GEOGRAPHY(POINT,4326)
CREATE EXTENSION IF NOT EXISTS postgis;

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_image_url VARCHAR(500),
    is_company BOOLEAN NOT NULL DEFAULT false,
    balance DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE companies (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    name VARCHAR(150) NOT NULL,
    ruc VARCHAR(20) UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE oscars (
    id SERIAL PRIMARY KEY,
    company_id INTEGER,
    code VARCHAR(100) UNIQUE NOT NULL,
    location GEOGRAPHY(POINT,4326) NOT NULL,
    name VARCHAR(150) NOT NULL,
    address VARCHAR(255),
    status VARCHAR(20) NOT NULL DEFAULT 'active'
        CHECK (status IN ('active', 'maintenance', 'inactive')),
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE oscar_status_log (
    id SERIAL PRIMARY KEY,
    oscar_id INTEGER NOT NULL,
    status VARCHAR(20) NOT NULL
        CHECK (status IN ('active', 'maintenance', 'inactive')),
    reason VARCHAR(255),
    changed_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE trash_type (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(100),
    value DECIMAL(8,4) NOT NULL DEFAULT 0
);

CREATE TABLE sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER,
    oscar_id INTEGER NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open'
        CHECK (status IN ('open', 'completed', 'cancelled')),
    total_value DECIMAL(10,2) NOT NULL DEFAULT 0,
    started_at TIMESTAMP NOT NULL DEFAULT NOW(),
    claimed_at TIMESTAMP,
    ended_at TIMESTAMP
);

CREATE TABLE session_item (
    id SERIAL PRIMARY KEY,
    session_id INTEGER NOT NULL,
    trash_type_id INTEGER NOT NULL,
    scanned_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    session_id INTEGER,
    reward_claim_id INTEGER,
    amount DECIMAL(10,2) NOT NULL,
    type VARCHAR(10) NOT NULL
        CHECK (type IN ('credit', 'debit')),
    note VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE reward (
    id SERIAL PRIMARY KEY,
    company_id INTEGER,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(500),
    stock INTEGER,
    is_active BOOLEAN NOT NULL DEFAULT true,
    valid_until TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE reward_claim (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    reward_id INTEGER NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending'
        CHECK (status IN ('pending', 'redeemed', 'expired')),
    code VARCHAR(100) UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    redeemed_at TIMESTAMP
);

-- INDEXES

CREATE INDEX idx_companies_user_id
    ON companies(user_id);

CREATE INDEX idx_oscars_location
    ON oscars USING GIST(location);

CREATE INDEX idx_oscars_company_id
    ON oscars(company_id);

CREATE INDEX idx_oscars_status
    ON oscars(status);

CREATE INDEX idx_oscar_status_log_oscar_id
    ON oscar_status_log(oscar_id);

CREATE INDEX idx_sessions_user_id
    ON sessions(user_id);

CREATE INDEX idx_sessions_oscar_id
    ON sessions(oscar_id);

CREATE INDEX idx_sessions_status
    ON sessions(status);

CREATE INDEX idx_sessions_started_at
    ON sessions(started_at);

CREATE INDEX idx_sessions_claimed_at
    ON sessions(claimed_at);

CREATE INDEX idx_session_item_session_id
    ON session_item(session_id);

CREATE INDEX idx_session_item_trash_type_id
    ON session_item(trash_type_id);

CREATE INDEX idx_session_item_scanned_at
    ON session_item(scanned_at);

CREATE INDEX idx_transactions_user_id
    ON transactions(user_id);

CREATE INDEX idx_transactions_session_id
    ON transactions(session_id);

CREATE INDEX idx_transactions_reward_claim_id
    ON transactions(reward_claim_id);

CREATE INDEX idx_transactions_type
    ON transactions(type);

CREATE INDEX idx_transactions_created_at
    ON transactions(created_at);

CREATE INDEX idx_reward_company_id
    ON reward(company_id);

CREATE INDEX idx_reward_is_active
    ON reward(is_active);

CREATE INDEX idx_reward_claim_user_id
    ON reward_claim(user_id);

CREATE INDEX idx_reward_claim_reward_id
    ON reward_claim(reward_id);

CREATE INDEX idx_reward_claim_status
    ON reward_claim(status);

-- COMMENTS

COMMENT ON COLUMN oscars.location
IS 'PostGIS ST_Point(lng, lat)';

COMMENT ON TABLE trash_type
IS 'Unidad de medida fija: gramos';

COMMENT ON COLUMN trash_type.value
IS 'créditos por gramo';

COMMENT ON COLUMN sessions.user_id
IS 'NULL hasta que el usuario escanea el QR';

COMMENT ON COLUMN sessions.claimed_at
IS 'cuando el usuario escaneó el QR';

COMMENT ON TABLE session_item
IS 'Cada fila = 1 ítem físico clasificado por el Oscar';

COMMENT ON COLUMN transactions.session_id
IS 'NULL si el movimiento es por recompensa';

COMMENT ON COLUMN transactions.reward_claim_id
IS 'NULL si el movimiento es por sesión';

COMMENT ON COLUMN reward.stock
IS 'NULL = ilimitado';

COMMENT ON COLUMN reward.valid_until
IS 'NULL = sin expiración';

COMMENT ON COLUMN reward_claim.code
IS 'código QR o voucher generado al reclamar';

-- FOREIGN KEYS

ALTER TABLE companies
ADD CONSTRAINT fk_companies_user
FOREIGN KEY (user_id)
REFERENCES users(id)
ON DELETE CASCADE;

ALTER TABLE oscars
ADD CONSTRAINT fk_oscars_company
FOREIGN KEY (company_id)
REFERENCES companies(id)
ON DELETE SET NULL;

ALTER TABLE oscar_status_log
ADD CONSTRAINT fk_oscar_status_log_oscar
FOREIGN KEY (oscar_id)
REFERENCES oscars(id)
ON DELETE CASCADE;

ALTER TABLE sessions
ADD CONSTRAINT fk_sessions_user
FOREIGN KEY (user_id)
REFERENCES users(id)
ON DELETE CASCADE;

ALTER TABLE sessions
ADD CONSTRAINT fk_sessions_oscar
FOREIGN KEY (oscar_id)
REFERENCES oscars(id)
ON DELETE CASCADE;

ALTER TABLE session_item
ADD CONSTRAINT fk_session_item_session
FOREIGN KEY (session_id)
REFERENCES sessions(id)
ON DELETE CASCADE;

ALTER TABLE session_item
ADD CONSTRAINT fk_session_item_trash_type
FOREIGN KEY (trash_type_id)
REFERENCES trash_type(id)
ON DELETE RESTRICT;

ALTER TABLE transactions
ADD CONSTRAINT fk_transactions_user
FOREIGN KEY (user_id)
REFERENCES users(id)
ON DELETE CASCADE;

ALTER TABLE transactions
ADD CONSTRAINT fk_transactions_session
FOREIGN KEY (session_id)
REFERENCES sessions(id)
ON DELETE SET NULL;

ALTER TABLE transactions
ADD CONSTRAINT fk_transactions_reward_claim
FOREIGN KEY (reward_claim_id)
REFERENCES reward_claim(id)
ON DELETE SET NULL;

ALTER TABLE reward
ADD CONSTRAINT fk_reward_company
FOREIGN KEY (company_id)
REFERENCES companies(id)
ON DELETE SET NULL;

ALTER TABLE reward_claim
ADD CONSTRAINT fk_reward_claim_user
FOREIGN KEY (user_id)
REFERENCES users(id)
ON DELETE CASCADE;

ALTER TABLE reward_claim
ADD CONSTRAINT fk_reward_claim_reward
FOREIGN KEY (reward_id)
REFERENCES reward(id)
ON DELETE RESTRICT;

-- DATA

INSERT INTO trash_type (name, icon, value)
VALUES
    ('Plástico', 'icon-plastic', 5),
    ('Papel/Cartón', 'icon-paper', 3),
    ('Lata', 'icon-can', 10),
    ('Orgánico', 'icon-organic', 2);

INSERT INTO oscars (company_id,name, code, location, address)
VALUES
    (NULL, "Oscar Federal" ,'OSCAR-001', ST_GeographyFromText('SRID=4326;POINT(-82.4285337 8.4563484)'), 'FEDERAL MALL, CHIRIQUÍ, PANAMÁ'),
    (NULL, "Oscar Mall Chiriqui" ,'OSCAR-002', ST_GeographyFromText('SRID=4326;POINT(-82.4637272 8.4322029)'), 'CHIRIQUI MALL, CHIRIQUÍ, PANAMÁ');