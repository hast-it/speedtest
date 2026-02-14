<?php

// Type of db: "mssql", "mysql", "sqlite" or "postgresql"
$db_type = 'mysql';
// Password to login to stats.php. Change this!!!
$stats_password = 'SpeedTestStats2026!';
// If set to true, test IDs will be obfuscated to prevent users from guessing URLs of other tests
$enable_id_obfuscation = true;
// If set to true, IP addresses will be redacted from IP and ISP info fields, as well as the log
$redact_ip_addresses = false;

// Sqlite3 settings
$Sqlite_db_file = __DIR__ . '/../../speedtest_telemetry.db';

// mssql settings
$MsSql_server = 'DB_HOSTNAME';
$MsSql_databasename = 'DB_NAME';
$MsSql_WindowsAuthentication = true;   //true or false
$MsSql_username = 'USERNAME';          //not used if MsSql_WindowsAuthentication is true
$MsSql_password = 'PASSWORD';          //not used if MsSql_WindowsAuthentication is true
$MsSql_TrustServerCertificate = true;  //true, false or comment out for driver default
//Download driver from https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server?view=sql-server-ver16

// Mysql settings
$MySql_username = 'speedtest';
$MySql_password = 'vK4nSgZ1UBv+IBoMTOxJWtYeaTNTbqCjAB2oY/9GHpQ=';
$MySql_hostname = 'localhost';
$MySql_databasename = 'speedtest_results';
$MySql_port = '3306';

// Postgresql settings
$PostgreSql_username = 'USERNAME';
$PostgreSql_password = 'PASSWORD';
$PostgreSql_hostname = 'DB_HOSTNAME';
$PostgreSql_databasename = 'DB_NAME';
