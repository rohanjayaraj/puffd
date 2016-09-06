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
         rowcount BIGINT NOT NULL,
         rowsize VARCHAR(50),
         network VARCHAR(50),
         workload LONGTEXT,

         description VARCHAR(50),
         disktype VARCHAR(50),
         numdisks INT,
         nummfs INT,
         tabletype VARCHAR(20),
         PRIMARY KEY (runid)
     );
CREATE INDEX tblycsbrun_ts ON tblycsbrun (timestamp);

DROP TABLE tblycsbstats;
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
         id INT,
         FOREIGN KEY (runid) REFERENCES tblycsbrun(runid)
     );

CREATE INDEX tblycsbstats_runid ON tblycsbstats (runid);

DROP TABLE tblycsbrunlog;
CREATE TABLE tblycsbrunlog (
         runid INT,
         wrkldid VARCHAR(50) NOT NULL,
         wrkldtype ENUM('LOAD', 'READ', 'SCAN', 'MIXED', 'DELETE'),
         id INT,
         log LONGTEXT,
         FOREIGN KEY (runid) REFERENCES tblycsbrun(runid)
     );

CREATE INDEX tblycsbrunlog_runid ON tblycsbrunlog (runid);

DROP TABLE tbldfsio;
CREATE TABLE tbldfsio (
         runid INT unsigned NOT NULL AUTO_INCREMENT,
         timestamp BIGINT NOT NULL,
         os VARCHAR(50),
         maprbuild VARCHAR(50),
         driver VARCHAR(20),
         description VARCHAR(50),
         disktype VARCHAR(50),
         hadoopversion VARCHAR(20),
         mfsinstances INT,
         writetp INT,
         readtp INT,
         teststatus VARCHAR(10),
         nodes VARCHAR(2000),
         joblogs LONGTEXT,
         configuration LONGTEXT,
         PRIMARY KEY (runid)
     );
CREATE INDEX tbldfsio_ts ON tbldfsio (timestamp);

DROP TABLE tblterasort;
CREATE TABLE tblterasort (
         runid INT unsigned NOT NULL AUTO_INCREMENT,
         timestamp BIGINT NOT NULL,
         os VARCHAR(50),
         build VARCHAR(50),
         driver VARCHAR(20),
         description VARCHAR(50),
         disktype VARCHAR(50),
         hadoopversion VARCHAR(20),
         mfsinstances INT,
         joblogs LONGTEXT,
         configuration LONGTEXT,
         nodes VARCHAR(2000),
         runtime INT,
         secure VARCHAR(10),
         encryption VARCHAR(10),
         avgmap INT,
         avgreduce INT,
         avgshuffle INT,
         avgmerge INT,
         teststatus VARCHAR(10),
         PRIMARY KEY (runid)
     );
CREATE INDEX tblterasort_ts ON tblterasort (timestamp);

DROP TABLE tblrwspeed;
CREATE TABLE tblrwspeed (
         runid INT unsigned NOT NULL AUTO_INCREMENT,
         timestamp BIGINT NOT NULL,
         os VARCHAR(50),
         build VARCHAR(50),
         mfsinstances INT,
         numsp INT,
         nodes VARCHAR(2000),
         description VARCHAR(50),
         repl1localread INT,
         repl1localwrite INT,
         repl1remoteread INT,
         repl1remotewrite INT,
         repl3localread INT,
         repl3localwrite INT,
         repl3remoteread INT,
         repl3remotewrite INT,
         status VARCHAR(10),
         disktype VARCHAR(50),
         driver VARCHAR(20),
         secure VARCHAR(10),
         networkencryption VARCHAR(10),
         hadoopversion VARCHAR(20),
         PRIMARY KEY (runid)
     );
CREATE INDEX tblrwspeed_ts ON tblrwspeed (timestamp);

DROP TABLE tblrubixruninfo;
CREATE TABLE tblrubixruninfo (
         runid INT unsigned NOT NULL AUTO_INCREMENT,
         timestamp BIGINT NOT NULL,
         platform ENUM('marlin', 'kafka'),
         os VARCHAR(50),
         disktype VARCHAR(10),
         buildversion VARCHAR(50),
         hostname VARCHAR(20),
         messagesize INT,
         duration INT,
         numtopics INT,
         numpartitions INT,
         servercount INT,
         numdisks INT,
         nummfs INT,
         numsp INT,
         description VARCHAR(50),
         PRIMARY KEY (runid)
     );
CREATE INDEX tblrubixruninfo_ts ON tblrubixruninfo (timestamp);

DROP TABLE tblrubixrundata;
CREATE TABLE tblrubixrundata (
         runid INT,
         testid VARCHAR(50),
         testtype ENUM('PRODUCER', 'CONSUMER', 'TANGO-CONSUMER', 'TANGO-PRODUCER', 'SLACKER-PRODUCER', 'SLACKER-CONSUMER'),
         replfactor INT,
         compression VARCHAR(10),
         numclients INT,
         throughput BIGINT,
         initthroughput BIGINT,
         ratedrop INT,
         avgtimetofinish INT,
         stddevduration INT,
         avglag BIGINT,
         avgofminlag BIGINT,
         avgofmaxlag BIGINT,
         absminlag BIGINT,
         absmaxlag BIGINT,
         FOREIGN KEY (runid) REFERENCES tblrubixruninfo(runid)
     );

CREATE INDEX tblrubixrundata_runid ON tblrubixrundata (runid);
