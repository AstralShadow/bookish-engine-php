create table UserResourceAccess(
	User int not null,
    Resource int not null,
    AccureTime datetime not null default current_timestamp,
    CurrencyValue int not null default 1,
    ProvidedBy int default null,
    
    primary key (User, Resource),
    Constraint UserResourceAccessUserId foreign key(User)
		references Users(UserId),
    Constraint UserResourceAccessResourceId foreign key(Resource)
		references Resources(ResourceId)
);

