#!/bin/sh

USERNAME='cincilms';
PASSWORD='oranges';

read -p "Username: " USERNAME;
stty -echo;
read -p "Password: " PASSWORD;
stty echo;

echo "";

mkdir -p DAO;

echo "Generating VO Source Files..."

ppdao --database CinciLMS --tables Courses,CourseRoles,FactCourseEnrollment --vo --include DAO/CoursesDAO.php --username ${USERNAME} --password ${PASSWORD} > CoursesVO.php;
ppdao --database CinciLMS --tables Users --vo --include DAO/UsersDAO.php --username ${USERNAME} --password ${PASSWORD} > UsersVO.php;
ppdao --database CinciLMS --tables CourseContent,ContentItems,ContentLinks,ContentItemAttachments,FactFolderContents --keys ContentLinks=DestinationID:ContentItemAttachments=ContentID --indexes FactFolderContents=IDX_PathIndex --vo --include DAO/ContentDAO.php --username ${USERNAME} --password ${PASSWORD} > ContentVO.php;

echo "Generating DAO Source Files..."

ppdao --database CinciLMS --tables Courses,CourseRoles,FactCourseEnrollment --dao --username ${USERNAME} --password ${PASSWORD} > DAO/CoursesDAO.php;
ppdao --database CinciLMS --tables Users --dao --username ${USERNAME} --password ${PASSWORD} > DAO/UsersDAO.php;
ppdao --username ${USERNAME} --password ${PASSWORD} --database CinciLMS --tables CourseContent,ContentItems,ContentLinks,ContentItemAttachments,FactFolderContents --keys ContentLinks=DestinationID:ContentItemAttachments=ContentID --indexes FactFolderContents=IDX_PathIndex --dao DAO/ContentDAO.php > DAO/ContentDAO.php;

