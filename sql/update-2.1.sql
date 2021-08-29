# Run this file to update the database for the core server update
alter table `groups`
    add column netid varchar(255);

create table `user_groups`
(
    `user_id`  int(10) unsigned not null,
    `group_id` int(10) unsigned not null,
    primary key (`user_id`, `group_id`)
) engine = InnoDB
  default charset = latin1;
insert into user_groups (user_id, group_id) select id as user_id, group_id from users;

alter table `users` drop column group_id;-- Run these sql commands to refactor the access_control table
delete from access_control where resource_type_id=2;
delete from access_control where participant_type_id=0;
alter table access_control drop column resource_type_id;
alter table access_control drop column participant_type_id;
alter table access_control drop column `id`;
delete from access_control where permission=0;
alter table access_control drop column permission;
alter table access_control rename column `participant_id` to `user_id`;
alter table access_control rename column `resource_id` to `device_id`;
alter table access_control add primary key (user_id,device_id);
drop table pages;