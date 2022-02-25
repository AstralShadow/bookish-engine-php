
create table Tags(
    TagId int primary key auto_increment,
    Name nvarchar(150) not null, 
    Description Text default null,

    Creator int not null,
    CreateTime datetime not null default current_timestamp,
    
    Constraint TagsCreatorId foreign key(Creator)
        references Users(UserId)
);

