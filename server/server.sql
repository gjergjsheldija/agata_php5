/*postgres*/
create database agataserver;
\c agataserver;
create sequence seq_userid;
create table agtuser (userid integer primary key default nextval('seq_userid'), login text not null, name text,password text, email text, isadmin boolean);
insert into agtuser values (1,'admin','Administrador',md5('admin'),'admin@solis.coop.br', true);

create sequence seq_projectid;
create table agtproject (projectid integer primary key default nextval('seq_projectid'), projectname text, basename text, description text, author text, date text, host text, baseuser text, pass text, type text, dict text);

create table agtaccess (userid integer, projectid integer, agttable text);
ALTER TABLE agtaccess ADD FOREIGN KEY (userid) REFERENCES agtuser(userid);
ALTER TABLE agtaccess ADD FOREIGN KEY (projectid) REFERENCES agtproject(projectid);


/*Você precisar inserir manualmente um projeto válido (que o admin consiga logar) na agtproject para poder inicializar o sistema*/
insert into agtproject values(1, 'Agataserver', 'agataserver', 'agataserver', 'agataserver', '22-06-1981', 'localhost', 'postgres', '', 'native-pgsql', '');
