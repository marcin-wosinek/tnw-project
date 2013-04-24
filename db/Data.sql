INSERT INTO `framework`.`sys_resource` (
`id` ,
`name` ,
`type` ,
`group` ,
`id_parent` ,
`edit_allowed`
)
VALUES (
NULL , 'default-donate', 'page', NULL , NULL , '1'
);

INSERT INTO `framework`.`sys_resource_privilege` (
`id` ,
`name` ,
`id_sys_resource` ,
`edit_allowed`
)
VALUES (
NULL , NULL , '5', '1'
);

INSERT INTO `framework`.`sys_role_resource_privilege` (
`id` ,
`id_sys_role` ,
`id_sys_resource_privilege` ,
`permission` ,
`edit_allowed`
)
VALUES (
NULL , '1', '5', 'allow', '1'
);

