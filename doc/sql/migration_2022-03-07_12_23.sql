use learning_res_simple;

alter table ResourceFeedback drop column Rating;
alter table ResourceFeedback modify column Message text not null;

create table ResourceRatings(
    Resource int not null,
    User int not null,

    Rating tinyint not null,

    Constraint ResourceRatingResourceId
        foreign key(Resource)
        references Resources(ResourceId),
    Constraint ResourceRatingUserID
        foreign key(User)
        references Users(UserId)
);
