create table if not exists account_book (
  id int unsigned not null auto_increment PRIMARY KEY,
  calc tinyint not null,
  adate date not null,
  note varchar(200),
  amnt int not null,
  serv varchar(100) not null,
  lctg varchar(100),
  mctg varchar(100),
  memo varchar(200),
  transfer tinyint not null,
  mfid varchar(50) not null unique,
  updated datetime not null,
  created datetime not null
) DEFAULT CHARSET=utf8mb4;
