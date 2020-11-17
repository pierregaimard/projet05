-- -------------------------------- --
-- ---------- DataBase ------------ --
-- -------------------------------- --

USE dbs809996;

SET lc_time_names = 'fr_FR';


-- -------------------------------- --
-- ----------- Tables ------------- --
-- -------------------------------- --


-- ----------------- --
-- Table user_status --
-- ----------------- --
CREATE TABLE user_status (
    id     TINYINT     UNSIGNED NOT NULL AUTO_INCREMENT,
    status VARCHAR(10) NOT NULL,
    PRIMARY KEY (id)
)
    ENGINE=INNODB
    DEFAULT CHARSET=utf8mb4;


-- ---------- --
-- Table user --
-- ---------- --
CREATE TABLE user (
    id                 SMALLINT         UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name         VARCHAR(30)      NOT NULL,
    last_name          VARCHAR(30)      NOT NULL,
    email              VARCHAR(50)      NOT NULL,
    password           VARCHAR(120)     NOT NULL,
    id_status          TINYINT UNSIGNED NOT NULL,
    last_security_code DATE,
    bad_credentials    TINYINT          UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_user_id_status FOREIGN KEY (id_status) REFERENCES user_status (id),
    CONSTRAINT UNIQUE INDEX ind_uni_email (email) -- user email is used for login so it must be unique
)
    ENGINE=INNODB
    DEFAULT CHARSET=utf8mb4;


-- --------------- --
-- Table user_role --
-- --------------- --
CREATE TABLE user_role (
    id   TINYINT    UNSIGNED NOT NULL AUTO_INCREMENT,
    role VARCHAR(6) NOT NULL,
    PRIMARY KEY (id)
)
    ENGINE=INNODB
    DEFAULT CHARSET=utf8mb4;


-- ------------------ --
-- Table as_user_role --
-- ------------------ --
CREATE TABLE as_user_role (
    id_user      SMALLINT UNSIGNED NOT NULL,
    id_user_role TINYINT  UNSIGNED NOT NULL,
    PRIMARY KEY (id_user, id_user_role),
    CONSTRAINT fk_as_user_role_id_user      FOREIGN KEY (id_user)      REFERENCES user (id)      ON DELETE CASCADE,
    CONSTRAINT fk_as_user_role_id_user_role FOREIGN KEY (id_user_role) REFERENCES user_role (id)
)
    ENGINE=INNODB,
    DEFAULT CHARSET=utf8mb4;


-- --------------- --
-- Table blog_post --
-- --------------- --
CREATE TABLE blog_post (
    id               SMALLINT     UNSIGNED NOT NULL AUTO_INCREMENT,
    title            VARCHAR(80)  NOT NULL,
    chapo            TINYTEXT     NOT NULL,
    content          TEXT         NOT NULL,
    creation_time    DATETIME     NOT NULL,
    last_update_time DATETIME     NOT NULL,
    id_user          SMALLINT     UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_blog_post_id_user FOREIGN KEY (id_user) REFERENCES user (id) ON DELETE CASCADE
)
    ENGINE=INNODB,
    DEFAULT CHARSET=utf8mb4;


-- ------------------------------ --
-- Table blog_post_comment_status --
-- ------------------------------ --
CREATE TABLE blog_post_comment_status (
    id     TINYINT     UNSIGNED NOT NULL AUTO_INCREMENT,
    status VARCHAR(10) NOT NULL,
    PRIMARY KEY (id)
)
    ENGINE=INNODB
    DEFAULT CHARSET=utf8mb4;


-- ----------------------- --
-- Table blog_post_comment --
-- ----------------------- --
CREATE TABLE blog_post_comment (
    id                          INT      UNSIGNED NOT NULL AUTO_INCREMENT,
    comment                     TINYTEXT NOT NULL,
    time                        DATETIME NOT NULL,
    id_blog_post                SMALLINT UNSIGNED NOT NULL,
    id_user                     SMALLINT UNSIGNED NOT NULL,
    id_blog_post_comment_status TINYINT  UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_blog_post_comment_id_blog_post                FOREIGN KEY (id_blog_post)                REFERENCES blog_post (id)                ON DELETE CASCADE,
    CONSTRAINT fk_blog_post_comment_id_user                     FOREIGN KEY (id_user)                     REFERENCES user (id)                     ON DELETE CASCADE,
    CONSTRAINT fk_blog_post_comment_id_blog_post_comment_status FOREIGN KEY (id_blog_post_comment_status) REFERENCES blog_post_comment_status (id)
)
    ENGINE=INNODB,
    DEFAULT CHARSET=utf8mb4;


-- --------------------------------- --
-- -------- tables triggers -------- --
-- --------------------------------- --


DELIMITER |


-- ------------------ --
-- blog_post triggers --
-- ------------------ --
-- BEFORE INSERT --
CREATE TRIGGER before_insert_blog_post BEFORE INSERT
ON blog_post FOR EACH ROW
BEGIN
    SET NEW.creation_time = NOW();    -- set blog_post creation time to now on insertion --
    SET NEW.last_update_time = NOW(); -- set blog_post last update time to now on insertion --
END |

-- BEFORE UPDATE --
CREATE TRIGGER before_update_blog_post BEFORE UPDATE
ON blog_post FOR EACH ROW
BEGIN
    SET NEW.last_update_time = NOW(); -- update last update time to now when updating blog post --
END |

-- ------------------------- --
-- blog_post_comment_trigger --
-- ------------------------- --
-- BEFORE INSERT --
CREATE TRIGGER before_insert_blog_post_comment BEFORE INSERT
ON blog_post_comment FOR EACH ROW
BEGIN
    SET NEW.time = NOW(); -- set blog post comment insertion time to now on insertion --
END |


DELIMITER ;


-- --------------------------------- --
-- -------- tables fixtures -------- --
-- --------------------------------- --


-- -------------------- --
-- user_status fixtures --
-- -------------------- --
INSERT INTO user_status
    (status)
VALUES
    ('VALIDATION'), ('ACTIVE'), ('LOCKED');

-- ------------------ --
-- user_role fixtures --
-- ------------------ --
INSERT INTO user_role
    (role)
VALUES
    ('ADMIN'), ('MEMBER');

-- -------------------------- --
-- blog_post_comment_fixtures --
-- -------------------------- --
INSERT INTO blog_post_comment_status
    (status)
VALUES
    ('VALIDATION'), ('APPROVED');
