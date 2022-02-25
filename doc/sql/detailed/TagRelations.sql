create table TagRelations(
    Supertag int not null,
    Subtag int not null,
    Creator int not null,
    CreateTime datetime not null default current_timestamp,

    primary key(Supertag, Subtag),
    Constraint TagRelationsSuperTagId foreign key(Supertag)
        references Tags(TagId),
    Constraint TagRelationsSubTagId foreign key(Subtag)
        references Tags(TagId),
    Constraint TagRelationsCreatorId foreign key(Creator)
        references Users(UserId)
);

