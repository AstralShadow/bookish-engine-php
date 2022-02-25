create table Roles(
	RoleId int primary key auto_increment,
    Name nvarchar(80) not null,
    
    CanCreateRoles bool not null default false,
    CanGiveRoles bool not null default false,

    CanBlockUsers bool not null default false,


    CanResolveReports bool not null default false,

    CanApproveResources bool not null default false,
    
    CanCreateTags bool not null default false,
    CanApproveTags bool not null default false,
    CanProposeTags bool not null default false
);

insert into Roles value (null, "admin",       1, 1, 1, 1, 1, 1, 1, 1);
insert into Roles value (null, "moderator",   0, 0, 1, 1, 1, 1, 1, 1);
insert into Roles value (null, "veteran",     0, 0, 0, 0, 1, 1, 1, 1);
insert into Roles value (null, "member",      0, 0, 0, 0, 0, 0, 0, 1);
