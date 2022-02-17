# Grants admin permissions to <username> 

use learning_res_simple;

insert into UserRoles value (
	(select u.UserId from Users as u where u.Name = "<username>"),
    (select r.RoleId from Roles as r where r.Name = "admin"),
    current_timestamp,
    (select u.UserId from Users as u where u.Name = "<username>"),
    "Default system administrator"
);

