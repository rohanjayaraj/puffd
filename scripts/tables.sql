DROP DATABASE perfdb;
CREATE DATABASE perfdb;
USE perfdb;
CREATE TABLE tblycsbrun (
         runid INT unsigned NOT NULL AUTO_INCREMENT,
         timestamp BIGINT NOT NULL,
         os VARCHAR(50),
         build VARCHAR(50),
         driver VARCHAR(20),
         totalcpus INT,
         totalmemory INT,
         totalspace INT,
         numclients INT,
         numnodes INT,

         numtables INT NOT NULL,
         numregions INT,
         datasize BIGINT NOT NULL,
         rowsize VARCHAR(50),
         network VARCHAR(50),
         
         workload LONGTEXT,
         PRIMARY KEY (runid)
     );
CREATE INDEX tblycsbrun_ts ON tblycsbrun (timestamp);

CREATE TABLE tblycsbstats (
         runid INT,
         wrkldid VARCHAR(50) NOT NULL,
         wrkldtype ENUM('LOAD', 'READ', 'SCAN', 'MIXED', 'DELETE'),
         threads INT,
         throughput INT,
         wavg INT DEFAULT NULL,
         wmin INT DEFAULT NULL,
         wmax INT DEFAULT NULL,
         wp95 TINYINT DEFAULT NULL,
         wp99 TINYINT DEFAULT NULL,
         ravg INT DEFAULT NULL,
         rmin INT DEFAULT NULL,
         rmax INT DEFAULT NULL,
         rp95 TINYINT DEFAULT NULL,
         rp99 TINYINT DEFAULT NULL,
         FOREIGN KEY (runid) REFERENCES tblycsbrun(runid)
     );

CREATE INDEX tblycsbstats_runid ON tblycsbstats (runid);
