-- Updates from version 1.33 to 1.34

-- Table Modifications

IF NOT EXISTS (SELECT * FROM SysObjects O INNER JOIN SysColumns C ON O.ID=C.ID
		WHERE ObjectProperty(O.ID,'IsUserTable')=1 AND O.Name='Settings' AND C.Name='IndicatePrivateArticle') 
	Alter Table Settings Add [IndicatePrivateArticle] [int] NULL 
;

-- Updates


if NOT EXISTS (select ID from FieldDetails where TableName = 'Articles' and FieldName = 'ViewableByG_S') 
INSERT INTO FieldDetails VALUES ('12/10/2007','SDrew','12/10/2007','SDrew','Articles','ViewableBy','ViewableByG_S','NO',null,'Submitter;Administrators','Everyone','DropList',' ; ,Public;1,Group Members;2,Editors;4,Administrators;8',null,10,10,'field','style=''width:175px''',null,null,null)

if NOT EXISTS (select ID from FieldDetails where TableName = 'Settings' and FieldName = 'IndicatePrivateArticle') 
	INSERT INTO FieldDetails VALUES ('12/10/2007','SDrew',null,null,'Settings','IndicatePrivateArticle','IndicatePrivateArticle','No','If checked then a lock icon is added beside the title to incidate the article is not publicly viewable.','Submitter;Administrators','Everyone','CheckBox','1',null,null,null,'field',null,null,null,null)


update Settings set DBVersion  = '1.34' where ID=1
update Settings set AppVersion = '1.34' where ID=1
update Settings set DBLastUpdate = GetDate() where ID=1

;

