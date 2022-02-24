
create user 'student_app_2'@'localhost' identified by 'student_app_2_password';

drop database learning_res_simple;

create database learning_res_simple;
grant all privileges on learning_res_simple.* to 'student_app_2'@'localhost';
# revoke privileges on learning_resources.* from 'student_app_2'@'localhost';
flush privileges;

use learning_res_simple;
# Data types #
/*
create table FileTypes(
	FileTypeId int primary key auto_increment,
    MimeType varchar(80) not null,
    External bool not null default false
    # in case of external resource, the blob contains path to the file
);
insert into FileTypes(MimeType, External) values
	("image/png", 0),
    ("image/jpeg", 0),
    ("application/*", 1);
# More to be added later
*/

# Users and roles #

create table Users(
	UserId int primary key auto_increment,

    Name nvarchar(80) not null,
    Password binary(60) not null,
    # Email nvarchar(120) not null,
    # EmailConfirmed bool not null default false,

    Scrolls int not null default 0,

	AvatarMime enum("image/jpeg", "image/png", "image/gif") default null,
    Avatar nvarchar(200) default null,
    CreateTime datetime not null default current_timestamp,

    BlockTime datetime default null,
    BlockedBy int default null,
    BlockReason text default null,
    
    #Constraint UsersAvatarType foreign key(AvatarType)
	#	references FileTypes(FileTypeId),
    constraint UsersBlockedBy foreign key(BlockedBy)
        references Users(UserId)
);

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

create table Roles(
	RoleId int primary key auto_increment,
    Name nvarchar(80) not null,
    
    CanCreateRoles bool not null default false,
    CanGiveRoles bool not null default false,

    CanBlockUsers bool not null default false,

   # CanGiveawayResources bool not null default false, # owner of resource can

    CanResolveReports bool not null default false,
   # CanCreateReportReasons bool not null default false,
   # CanReportCustomReasons bool not null default false,

    CanApproveResources bool not null default false,
    
    CanCreateTags bool not null default false,
    CanApproveTags bool not null default false, # owned of resource can
    CanProposeTags bool not null default false # owner of resource can
);
insert into Roles value (null, "admin",       1, 1, 1, /*1,*/ 1, /* 1, 1,*/ 1, 1, 1, 1);
insert into Roles value (null, "moderator",   0, 0, 1, /*0,*/ 1, /* 1, 1,*/ 1, 1, 1, 1);
insert into Roles value (null, "veteran",     0, 0, 0, /*0,*/ 0, /* 0, 1,*/ 1, 1, 1, 1);
insert into Roles value (null, "member",      0, 0, 0, /*0,*/ 0, /* 0, 1,*/ 0, 0, 0, 1);
# admin is given to me (as website host)
# moderator is given to people that admin approves personally.
# veteran is given to loyal people (they use the app a lot).
# member is given to people who actually use the app.

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


# Resources #

create table Resources(
	ResourceId int primary key auto_increment,
    Name nvarchar(150) not null,
    Owner int not null,
    Description text default null,
    CreateTime datetime not null default current_timestamp,
    
    Data varchar(200) default null,
    DataName nvarchar(200) default null,
    DataSize int default null,
    DataMime varchar(200) default null,
    Preview varchar(200) default null,
    PreviewName nvarchar(200) default null,
    PreviewSize int default 0,
    PreviewMime varchar(200) default null,

    Price int default 1,
    
    ApproveNote text default null,
    ApprovedBy int default null,
    ApproveTime datetime default null,
    
	Constraint ResourcesDataOwnedId foreign key(Owner)
		references Users(UserId),
    #Constraint ResourcesDataType foreign key(DataType)
	#	references FileTypes(FileTypeId),
    #Constraint ResourcesDataPreviewType foreign key(PreviewType)
	#	references FileTypes(FileTypeId),
	Constraint ResourcesDataApprovedBy foreign key(ApprovedBy)
		references Users(UserId)
);

create table UserResourceAccess(
	User int not null,
    Resource int not null,
    AccureTime datetime not null default current_timestamp,
    CurrencyValue int not null default 1, # for the sake of donating custom value
    ProvidedBy int default null, # for the sake of gifting the resource
    
    primary key (User, Resource),
    Constraint UserResourceAccessUserId foreign key(User)
		references Users(UserId),
    Constraint UserResourceAccessResourceId foreign key(Resource)
		references Resources(ResourceId)
);


# Feedback #

create table ResourceFeedback(
    ResourceFeedbackId int primary key auto_increment,
    
    Resource int not null,
    User int not null,
    CreateTime datetime not null default current_timestamp,

    Message text,
    Rating tinyint not null, # values between 0 and 100, implementation defined
    # Public bool not null default true, # May change that to more sophiscated comment section

    Constraint ResourceFeedbacksResourceId foreign key(Resource)
        references Resources(ResourceId),
    Constraint ResourceFeedbacksUserID foreign key(User)
        references Users(UserId)
);


# Report #

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

# can be added after for the better comment system
# create table ResourceFeedbackReports();


# Tags #

create table Tags(
    TagId int primary key auto_increment,
    Name nvarchar(150) not null, 
    Description Text default null,
    # they are really supposed to be self-explainatory, so ideally, don not use this

    Creator int not null,
    CreateTime datetime not null default current_timestamp,
    
    Constraint TagsCreatorId foreign key(Creator)
        references Users(UserId)
);

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
