CREATE TABLE Sessions
(
    SessionId INT PRIMARY KEY AUTO_INCREMENT,
    Token CHAR(36) NOT NULL UNIQUE,
    User INT NOT NULL,
    Created DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (User)
        REFERENCES Users(UserId)
);

CREATE INDEX idx_sessions ON Sessions (Token); 
