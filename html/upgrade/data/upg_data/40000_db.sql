
 INSERT IGNORE INTO `core_menu` ( `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform` )
VALUES
	( '_MAIL_CONFIG', '', '1', TRUE, TRUE, (SELECT `idMenu` FROM ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_CONFIG_SYS' ) tbl), NULL, 'framework' );

 INSERT IGNORE INTO `core_menu` ( `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform` )
VALUES
	( '_DOMAIN_CONFIG', '', '1', TRUE, TRUE, (SELECT `idMenu` FROM ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_CONFIG_SYS' ) tbl), NULL, 'framework' );

INSERT IGNORE INTO `core_menu_under` ( `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path` )
VALUES
	( ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_MAIL_CONFIG' LIMIT 1 ) ,
	'mailconfig',
	'_MAIL_CONFIG',
	NULL,
	'view',
	'framework',
	1,
	NULL,
	NULL,
	'adm/mailconfig/show' 
	);

INSERT IGNORE INTO `core_menu_under` ( `idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path` )
VALUES
	( ( SELECT `idMenu` FROM `core_menu` WHERE NAME = '_DOMAIN_CONFIG' LIMIT 1 ) ,
	'domainconfig',
	'_DOMAIN_CONFIG',
	NULL,
	'view',
	'framework',
	1,
	NULL,
	NULL,
	'adm/domainconfig/show' 
	);

CREATE TABLE IF NOT EXISTS core_mail_configs (
    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title varchar(255),
    system boolean not null default 0
);

CREATE TABLE IF NOT EXISTS core_mail_configs_fields (
    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    mailConfigId int,
    type varchar(255),
	value varchar(255)
);

CREATE TABLE IF NOT EXISTS core_domain_configs (
    id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title varchar(255),
	domain varchar(255),
	parentId int NULL DEFAULT NULL,
	template varchar(255),
    orgId int NOT NULL,
	mailConfigId int NOT NULL,
);
