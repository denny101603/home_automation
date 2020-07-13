create table temperatures(moduleID int not null, dtime datetime not null, temperature float not null, humidity float not null,
	primary key(moduleID, dtime));

create table doors(moduleID int not null, dtime datetime not null, state boolean not null, 
	primary key(moduleID, dtime));

create table state(id int not null, roomLED int not null, houseLock boolean not null, heating int not null, heatingSettings text,
	primary key(id));

create table rules(id int not null auto_increment, event int not null, eventSpec text, action int not null, actionSpec text,
	primary key(id));

create table modulesState(id int not null, state int not null, lastOnline datetime,
	primary key(id));

create table rfidCards(uid varchar(128) not null, owner varchar(128),
	primary key(uid));

create table securityHistory(newState boolean not null, dtime datetime not null, way text not null, 
	primary key(dtime));

create table motionHistory(moduleID int not null, dtime datetime not null, stopTime datetime,
	primary key(moduleID, dtime));

INSERT INTO `state`(`id`, `roomLED`, `houseLock`, `heating`) VALUES (1, 3, 0, 2);
INSERT INTO `modulesState`(`id`, `state`) VALUES (40, 0);
INSERT INTO `modulesState`(`id`, `state`) VALUES (50, 0);
INSERT INTO `modulesState`(`id`, `state`) VALUES (60, 0);
INSERT INTO `modulesState`(`id`, `state`) VALUES (70, 0);
INSERT INTO `modulesState`(`id`, `state`) VALUES (80, 0);
