create table UserRoles(
	User int not null,
    Role int not null,
    
    CreateTime datetime not null default current_timestamp,
    AssignedBy int not null,
    Reason text default null,
    
    primary key (User, Role),
    
    CONSTRAINT RoleUsersUser FOREIGN KEY (User)
		REFERENCES Users(UserId),
    CONSTRAINT RoleUsersAssigner FOREIGN KEY (AssignedBy)
		REFERENCES Users(UserId),
    CONSTRAINT RoleUsersRole FOREIGN KEY (Role)
		REFERENCES Roles(RoleId)
);
