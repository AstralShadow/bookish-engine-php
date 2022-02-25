create table ResourceTags(
    Resource int not null,
    Tag int not null,
    
    ProposedBy int not null,
    ProposeTime datetime not null default current_timestamp,

    ApprovedBy int default null,
    ApproveTime datetime default null,


    primary key(Resource, Tag),
    constraint ResourceTagsResourceId foreign key(Resource)
        references Resources(ResourceId),
    constraint ResourceTagsTagId foreign key(Tag)
        references Tags(TagId),
    constraint ResourceTagsProposedBy foreign key(ProposedBy)
        references Users(UserId),
    constraint ResourceTagsApprovedBy foreign key(ApprovedBy)
        references Users(UserId)
);
