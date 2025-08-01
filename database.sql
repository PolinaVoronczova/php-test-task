CREATE TABLE posts (
    id          bigint PRIMARY KEY,
    user_id     bigint,
    title       varchar(255),
    body        text,
    created_at  timestamp
);

CREATE TABLE comments (
    id            bigint PRIMARY KEY,
    post_id       bigint REFERENCES posts (id),
    name          varchar(255),
    email         varchar(50),
    body          text,
    created_at    timestamp
);