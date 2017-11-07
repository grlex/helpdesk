create database if not exists helpdesk;
use helpdesk;
set foreign_key_checks =0;

drop table if exists active;
drop table if exists category;
drop table if exists comment;
drop table if exists department;
drop table if exists file;
drop table if exists lifecycle_step;
drop table if exists request;
drop table if exists request_file;
drop table if exists thread;
drop table if exists user;

#set foreign_key_checks = 1;

CREATE TABLE active
(
  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  department_id INT,
  cab_number VARCHAR(50) NOT NULL,
  FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE SET NULL
);
CREATE INDEX IDX_4B1EFC02AE80F5DF ON active (department_id);


CREATE TABLE category
(
  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL
);


CREATE TABLE comment
(
  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  author_id INT,
  thread_id VARCHAR(255),
  body LONGTEXT NOT NULL,
  ancestors VARCHAR(1024) NOT NULL,
  depth INT NOT NULL,
  created_at DATETIME NOT NULL,
  state INT NOT NULL,
  raw_body LONGTEXT,
  FOREIGN KEY (thread_id) REFERENCES thread (id),
  FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE SET NULL
);
CREATE INDEX IDX_9474526CE2904019 ON comment (thread_id);
CREATE INDEX IDX_9474526CF675F31B ON comment (author_id);


CREATE TABLE department
(
  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL
);


CREATE TABLE file
(
  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  original_name VARCHAR(100) NOT NULL,
  confirmed TINYINT DEFAULT 0,
  filename VARCHAR(50) NOT NULL,
  created DATETIME NOT NULL
);
CREATE UNIQUE INDEX UNIQ_8C9F36103C0BE965 ON file (filename);


CREATE TABLE lifecycle_step
(
  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  comment_id INT,
  request_id INT,
  request_status INT NOT NULL,
  user_id INT,
  datetime DATETIME NOT NULL,
  FOREIGN KEY (request_id) REFERENCES request (id) ON DELETE CASCADE,
  FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL
);
CREATE UNIQUE INDEX UNIQ_EC3CE800F8697D13 ON lifecycle_step (comment_id);
CREATE INDEX IDX_EC3CE800427EB8A5 ON lifecycle_step (request_id);
CREATE INDEX IDX_EC3CE800A76ED395 ON lifecycle_step (user_id);


CREATE TABLE request
(
  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  active_id INT,
  category_id INT,
  user_id INT,
  executor_id INT,
  name VARCHAR(50) NOT NULL,
  description LONGTEXT,
  status INT DEFAULT 1 NOT NULL,
  priority INT DEFAULT 2 NOT NULL,
  FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL,
  FOREIGN KEY (active_id) REFERENCES active (id) ON DELETE SET NULL,
  FOREIGN KEY (executor_id) REFERENCES user (id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL
);
CREATE INDEX IDX_3B978F9F12469DE2 ON request (category_id);
CREATE INDEX IDX_3B978F9F27C382C7 ON request (active_id);
CREATE INDEX IDX_3B978F9F8ABD09BB ON request (executor_id);
CREATE INDEX IDX_3B978F9FA76ED395 ON request (user_id);


CREATE TABLE request_file
(
  request_id INT NOT NULL,
  file_id INT NOT NULL,
  PRIMARY KEY (request_id, file_id),
  FOREIGN KEY (request_id) REFERENCES request (id) ON DELETE CASCADE,
  FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE
);
CREATE INDEX IDX_F33811EA427EB8A5 ON request_file (request_id);
CREATE INDEX IDX_F33811EA93CB796C ON request_file (file_id);


CREATE TABLE thread
(
  id VARCHAR(255) PRIMARY KEY NOT NULL,
  permalink VARCHAR(255) NOT NULL,
  is_commentable TINYINT NOT NULL,
  num_comments INT NOT NULL,
  last_comment_at DATETIME,
  request_id INT,
  FOREIGN KEY (request_id) REFERENCES request (id)
);
CREATE UNIQUE INDEX UNIQ_31204C83427EB8A5 ON thread (request_id);


set @role_admin = 1;
set @role_moderator = 2;
set @role_executor = 4;
set @role_user = 8;
CREATE TABLE user
(
  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  department_id INT,
  name VARCHAR(50) NOT NULL,
  login VARCHAR(50) NOT NULL,
  password VARCHAR(60) NOT NULL,
  position VARCHAR(50),
  roles_mask SMALLINT NOT NULL,
  removed TINYINT DEFAULT 0 NOT NULL,
  FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE SET NULL
);
CREATE INDEX IDX_8D93D649AE80F5DF ON user (department_id);



#
# TRIGGERS  
#
drop trigger if exists user_is_not_an_executor_anymore;
delimiter //
create trigger user_is_not_an_executor_anymore after update on user for each row
  BEGIN
    update request set executor_id=null where executor_id=new.id and not user_is_executor(new.roles_mask);
  END//
delimiter ;


#
# FUNCTIONS
#
drop function if exists user_is_executor;
delimiter //
create function user_is_executor(roles_mask smallint)
  RETURNS BOOL
  BEGIN 
    RETURN roles_mask & @role_executor;
  END//
delimiter ;

set FOREIGN_KEY_CHECKS = 1;


#
# TEMP DATA
#

insert into user(name, login, roles_mask, password, position) values ('admin', 'admin', @role_admin,  '$2y$13$No1.HRFzRXlqxf1rvevepu8EMbk4hvdeEQLcssFNR3VoFfUyk0wBm', 'Сис админ');
insert into user(name, login, roles_mask, password, position) values ('moder', 'moder', @role_moderator, '$2y$13$5H544x2ShZCuJhbDIHlL4ug40t4Phb96C1CxHINLFQCvjg97RqhHu', 'Менеджер');
insert into user(name, login, roles_mask, password, position) values ('exer', 'exer', @role_executor, '$2y$13$Nx5UeJZuxzw3LNbNlFEsSuZryGKtqTWvh/etpLuAg836G9.Qn4oXm', 'Техник');
insert into user(name, login, roles_mask, password, position) values ('user', 'user', @role_user, '$2y$13$vqmbP5wapZapIWv3Qqg60u6H2i9gsq3fIzT/UgRDYoO44feopO8Zq', 'Приемщик');


insert into category(name) values ('Компьютеры'),('Мебель');
insert into department(name) values ('Администрация'),('Бухгалтерия');
insert into active (cab_number, department_id) 
    values (
        410, 
        (select id from department where name='Администрация')
    );
insert into active (cab_number, department_id) 
    values (
      415,
      (select id from department where name='Администрация')
    );
insert into active (cab_number, department_id)
values (
  322,
  (select id from department where name='Бухгалтерия')
);










