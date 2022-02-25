create table Users(
    UserId int primary key auto_increment,

    Name nvarchar(80) not null,
    Password binary(60) not null,

    Scrolls int not null default 0,

	AvatarMime enum("image/jpeg", "image/png", "image/gif") default null,
    Avatar nvarchar(200) default null,
    CreateTime datetime not null default current_timestamp,

    BlockTime datetime default null,
    BlockedBy int default null,
    BlockReason text default null,
    
    constraint UsersBlockedBy foreign key(BlockedBy)
        references Users(UserId)
);
