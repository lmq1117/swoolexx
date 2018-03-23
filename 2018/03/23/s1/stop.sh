#! /bin/bash
ps -eaf |grep "timers.php" | grep -v "grep"| awk '{print $2}'|xargs kill -9
