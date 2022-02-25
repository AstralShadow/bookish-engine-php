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
	Constraint ResourcesDataApprovedBy foreign key(ApprovedBy)
		references Users(UserId)
);

