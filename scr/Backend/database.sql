CREATE TABLE Comments ( FileId varchar(255) NOT NULL, Id int NOT NULL, Text varchar(255) NOT NULL, ParentId int, CONSTRAINT uc_Comment UNIQUE (FileId,Id) );