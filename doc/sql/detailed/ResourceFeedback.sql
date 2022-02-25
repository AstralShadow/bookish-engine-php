create table ResourceFeedback(
    ResourceFeedbackId int primary key auto_increment,
    
    Resource int not null,
    User int not null,
    CreateTime datetime not null default current_timestamp,

    Message text,
    Rating tinyint not null,

    Constraint ResourceFeedbacksResourceId foreign key(Resource)
        references Resources(ResourceId),
    Constraint ResourceFeedbacksUserID foreign key(User)
        references Users(UserId)
);
