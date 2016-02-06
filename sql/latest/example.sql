-- example.sql
-- FreeNATS Example Settings
INSERT INTO fnnode(nodeid,nodename,nodedesc,hostname,nodeenabled,pingtest,weight) VALUES("freenats","FreeNATS","FreeNATS Server","127.0.0.1",1,1,10);