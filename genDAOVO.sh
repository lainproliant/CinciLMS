#!/bin/sh

read -p "Username: " USERNAME;
stty -echo;
read -p "Password: " PASSWORD;
stty echo;

mkdir -p site/lib/VO/DAO;

cd site/lib/VO

echo "";

echo "Generating VO Source Files..."

ppdao --database CinciLMS2 \
   --tables Courses,CourseRoles,FactCourseEnrollment \
   --keys FactCourseEnrollment=CourseID \
   --vo --include DAO/CoursesDAO.php --username ${USERNAME} --password ${PASSWORD} > CoursesVO.php;

ppdao --database CinciLMS2 \
   --tables Users \
   --vo --include DAO/UsersDAO.php --username ${USERNAME} --password ${PASSWORD} > UsersVO.php;

ppdao --database CinciLMS2 \
   --tables CourseContent,ContentItems,ContentLinks,ContentItemAttachments,FactFolderContents \
   --keys ContentLinks=DestinationID:ContentItemAttachments=ContentID \
   --indexes FactFolderContents=IDX_PathIndex \
   --vo --include DAO/ContentDAO.php --username ${USERNAME} --password ${PASSWORD} > ContentVO.php;

ppdao --database CinciLMS2 \
   --tables GradeColumns,Grades \
   --keys GradeColumns=CourseID \
   --vo --include DAO/GradesDAO.php --username ${USERNAME} --password ${PASSWORD} > GradesVO.php;

ppdao --database CinciLMS2 \
   --tables Assignments,AssignmentFileSubmissions,AssignmentTypes \
   --indexes AssignmentFileSubmissions=IDX_StudentAssignmentSubmissions \
   --keys Assignments=AssignmentID \
   --vo --include DAO/AssignmentDAO.php --username ${USERNAME} --password ${PASSWORD} > AssignmentVO.php;

echo "Generating DAO Source Files..."

ppdao --database CinciLMS2 \
   --tables Courses,CourseRoles,FactCourseEnrollment \
   --keys FactCourseEnrollment=CourseID \
   --dao --username ${USERNAME} --password ${PASSWORD} > DAO/CoursesDAO.php;

ppdao --database CinciLMS2 \
   --tables Users \
   --dao --username ${USERNAME} --password ${PASSWORD} > DAO/UsersDAO.php;

ppdao --database CinciLMS2 \
   --tables CourseContent,ContentItems,ContentLinks,ContentItemAttachments,FactFolderContents \
   --keys ContentLinks=DestinationID:ContentItemAttachments=ContentID \
   --indexes FactFolderContents=IDX_PathIndex \
   --dao --username ${USERNAME} --password ${PASSWORD} > DAO/ContentDAO.php;

ppdao --database CinciLMS2 \
   --tables GradeColumns,Grades \
   --keys GradeColumns=CourseID \
   --dao --username ${USERNAME} --password ${PASSWORD} > DAO/GradesDAO.php;

ppdao --database CinciLMS2 \
   --tables Assignments,AssignmentFileSubmissions,AssignmentTypes \
   --indexes AssignmentFileSubmissions=IDX_StudentAssignmentSubmissions \
   --keys Assignments=AssignmentID \
   --dao --username ${USERNAME} --password ${PASSWORD} > DAO/AssignmentDAO.php;

