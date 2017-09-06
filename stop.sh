#! /bin/bash
ps -eaf |grep "server.php" | grep -v "grep"| awk '{print $2}'|xargs kill -9
