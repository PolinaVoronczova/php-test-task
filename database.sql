CREATE TABLE posts (
    id          bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    user_id     bigint,
    title       varchar(255),
    body        text,
    created_at  timestamp
);

CREATE TABLE comments (
    id            bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    post_id       bigint REFERENCES posts (id),
    name          varchar(100),
    email         varchar(50),
    body          varchar(255),
    created_at    timestamp
);