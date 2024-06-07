-- Updates from version 1.26 to 1.27

-- Table Modifications

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='users' AND C.Name='BulletinEmail') 
	Alter Table users Add [BulletinEmail] [varchar] (3) default NULL
;


-- Inserts

if NOT EXISTS (select ID from FieldDetails where TableName = 'users' and FieldName = 'BulletinEmail') 
	INSERT INTO FieldDetails VALUES ('02/02/2006','sdrew',null,null,'users','BulletinEmail','BulletinEmail','No','If checked, you will receive Email notifications regarding Bulletins.','Submitter;Administrators','Everyone','CheckBox','Yes',null,null,null,null,null,null,null,null)
;

-- Updates
-- update users set BulletinEmail = 'Yes' where BulletinEmail is NULL
update Settings set DBVersion  = '1.27' where ID=1
update Settings set AppVersion = '1.27' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1
;

