create database learning_res_simple;
create user 'student_app_2'@'localhost' identified by 'student_app_2_password';
grant all privileges on learning_resources.* to 'student_app_2'@'localhost';
flush privileges;

use learning_res_simple;
# Data types #

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


# Users and roles #

create table Users(
	UserId int primary key auto_increment,

    Name nvarchar(80) not null,
    PasswordHash binary(60) not null,
    # Email nvarchar(120) not null,
    # EmailConfirmed bool not null default false,

	AvatarType int default null,
    Avatar blob default null,
    CreateTime datetime not null default current_timestamp,

    BlockTime datetime default null,
    BlockedBy int default null,
    BlockReason text default null,
    
    Constraint UsersAvatarType foreign key(AvatarType)
		references FileTypes(FileTypeId),
    constraint UsersBlockedBy foreign key(BlockedBy)
        references Users(UserId)
);

CREATE TABLE Sessions
(
    SessionId INT PRIMARY KEY AUTO_INCREMENT,
    token CHAR(36) NOT NULL UNIQUE,
    user INT NOT NULL,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user)
        REFERENCES Users(UserId)
);

create table Roles(
	RoleId int primary key auto_increment,
    Name nvarchar(80) not null,
    
    CanCreateRoles bool not null default false,
    CanGiveRoles bool not null default false,

    CanBlockUsers bool not null default false,

   # CanGiveawayResources bool not null default false, # owner of resource can

    CanResolveReports bool not null default false,
    CanCreateReportReasons bool not null default false,
    CanReportCustomReasons bool not null default false,

    CanApproveResources bool not null default false,
    CanCreateTags bool not null default false,
    CanApproveTags bool not null default false, # owned of resource can
    CanProposeTags bool not null default false # owner of resource can
);
insert into Roles value (null, "admin",       1, 1, 1, /*1,*/ 1, 1, 1, 1, 1, 1, 1);
insert into Roles value (null, "moderator",   0, 0, 1, /*0,*/ 1, 1, 1, 1, 1, 1, 1);
insert into Roles value (null, "veteran",     0, 0, 0, /*0,*/ 0, 0, 1, 1, 1, 1, 1);
insert into Roles value (null, "member",      0, 0, 0, /*0,*/ 0, 0, 1, 0, 0, 0, 1);
# There is better version in no-raw sql
# admin is given to me (as website host)
# moderator is given to people that admin approves personally.
# veteran is given to loyal people (they use the app a lot).
# member is given to people who actually use the app.

create table UserRoles(
	UserId int not null,
    RoleId int not null,
    CreateTime datetime not null default current_timestamp,
    AssignedBy int not null,
    Reason text default null,
    
    primary key (UserId, RoleId),
    
    CONSTRAINT RoleUsersUser FOREIGN KEY (UserId)
		REFERENCES Users(UserId),
    CONSTRAINT RoleUsersAssigner FOREIGN KEY (AssignedBy)
		REFERENCES Users(UserId),
    CONSTRAINT RoleUsersRole FOREIGN KEY (RoleId)
		REFERENCES Roles(RoleId)
);


# Resources #

create table Resources(
	ResourceId int primary key auto_increment,
    Name nvarchar(150) not null,
    OwnerId int not null,
    Description text default null,
    CreateTime datetime not null default current_timestamp,
    
    DataType int not null,
    DataName nvarchar(150) default null, # as the downloaded file name, if applicable (use for extension)
    Data blob not null,
    PreviewType int not null,
    PreviewName nvarchar(150) default null, # as the downloaded file name, if applicable (use for extension)
    Preview blob not null,
    
    Approved bool default null,
    ApproveNote text default null,
    ApprovedBy int default null,
    
	Constraint ResourcesDataOwnedId foreign key(OwnerId)
		references Users(UserId),
    Constraint ResourcesDataType foreign key(DataType)
		references FileTypes(FileTypeId),
    Constraint ResourcesDataPreviewType foreign key(PreviewType)
		references FileTypes(FileTypeId),
	Constraint ResourcesDataApprovedBy foreign key(ApprovedBy)
		references Users(UserId)
);

create table UserResourceAccess(
	UserId int not null,
    ResourceId int not null,
    AccureTime datetime not null default current_timestamp,
    CurrencyValue int not null default 1, # for the sake of donating custom value
    ProvidedBy int default null, # for the sake of gifting the resource
    
    primary key (UserId, ResourceId),
    Constraint UserResourceAccessUserId foreign key(UserId)
		references Users(UserId),
    Constraint UserResourceAccessResourceId foreign key(ResourceId)
		references Resources(ResourceId)
);


# Feedback #

create table ResourceFeedbacks(
    ResourceFeedbackId int primary key auto_increment,
    ResourceId int not null,
    UserId int not null,
    Message text,
    Rating tinyint not null, # values between 0 and 100, implementation defined
    # Public bool not null default true, # May change that to more sophiscated comment section

    Constraint ResourceFeedbacksResourceId foreign key(ResourceId)
        references Resources(ResourceId),
    Constraint ResourceFeedbacksUserID foreign key(UserId)
        references Users(UserId)
);


# Report #

create table ReportReasons(
    ReportReasonId int primary key auto_increment, # with null meaning Other
    Name nvarchar(150)
);
# example values: inappropriate content; wrongly tagged content; inaccurate info
# different from the preview; exactly same as the preview (without mentioned)

create table ResourceReports(
    ResourceReportId int primary key auto_increment,
    ResourceId int not null,
    CreateTime datetime not null default current_timestamp,

    FiredBy int not null,
    ReportReasonId int default null,
    Message Text default null,

    ResolvedBy int default null,
    ResolveMessage Text default null,
    ResolveTime datetime default null,

    constraint ResourceReportsResourceId foreign key(ResourceId)
        references Resources(ResourceId),
    constraint ResourceReportsFiredBy foreign key(FiredBy)
        references Users(UserId),
    constraint ResourceReportsReportReasonId foreign key(ReportReasonId)
        references ReportReasons(ReportReasonId),
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

    CreatorId int not null,
    CreateTime datetime not null default current_timestamp,
    
    Constraint TagsCreatorId foreign key(CreatorId)
        references Users(UserId)
);

create table TagRelations(
    SuperTagId int not null,
    SubTagId int not null,
    CreatorId int not null,
    CreateTime datetime not null default current_timestamp,

    primary key(SuperTagId, SubTagId),
    Constraint TagRelationsSuperTagId foreign key(SuperTagId)
        references Tags(TagId),
    Constraint TagRelationsSubTagId foreign key(SubTagId)
        references Tags(TagId)
);

create table ResourceTags(
    ResourceId int not null,
    TagId int not null,
    
    ProposedBy int not null,
    ProposeTime datetime not null default current_timestamp,

    ApprovedBy int not null,
    ApproveTime datetime default null,

    primary key(ResourceId, TagId),
    constraint ResourceTagsResourceId foreign key(ResourceId)
        references Resources(ResourceId),
    constraint ResourceTagsTagId foreign key(TagId)
        references Tags(TagId),
    constraint ResourceTagsProposedBy foreign key(ProposedBy)
        references Users(UserId),
    constraint ResourceTagsApprovedBy foreign key(ApprovedBy)
        references Users(UserId)
);
