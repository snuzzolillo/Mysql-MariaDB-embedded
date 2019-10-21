# Programmatic and Compound Statements (MariaDB) embedded in a Server Script (PHP)

###### Note: The code shown here is part of a larger project implementation called WEB APPLICATION FULLSTACK FRAMEWORK.

If you are a SQL database enthusiast and know about the use of database languages and know the database engine (RDBMS) power then this may be of interest to you. The versions before 10.1.1 of MariaDB did not allow Programmatic and Compound Statements outside of a procedure, functions or triggers (we usually call them "anonymous blocks"). From version 10.1.1 of MariaDB they are possible. This opens a door to the possibility of making full use of the maximum potential of the database engine (RDBMS) during the "data transformation" or performing of the "business rules". This potential also includes the control of transactions, that is, you deciding when to make a COMMIT or when to make a ROLLBACK. All within a "BEGIN ... END;" can be treated as one "transaction."

At the time of developing this code, I did not find any indication that the latest version of MySql ([MySQL 8.0.18](https://dev.mysql.com/doc/relnotes/mysql/8.0/en/), General Availability 2019-10-14) has this capability.

[Let's start with a very simple example](doc/TOPIC_01.md)
