create table ResourceReports(
    ResourceReportId int primary key auto_increment,
    Resource int not null,
    CreateTime datetime not null default current_timestamp,

    FiredBy int not null,
    Message Text default null,

    ResolvedBy int default null,
    ResolveMessage Text default null,
    ResolveTime datetime default null,

    constraint ResourceReportsResourceId foreign key(Resource)
        references Resources(ResourceId),
    constraint ResourceReportsFiredBy foreign key(FiredBy)
        references Users(UserId),
    constraint ResourceReportsResolvedBy foreign key(ResolvedBy)
        references Users(UserId)
);

