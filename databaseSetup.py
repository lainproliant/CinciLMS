#!/usr/bin/env python2

# Database Setup Script for the Cincinnatus Learning Management System
# (c) 2011 Lee Supe
# Released under the GNU General Public License, version 3.

#-----------------------------------------------------------------------------
# Notes regarding Course and Content Access Permissions
#
# The access permission flags for courses and content items are stored as a
# MySQL SET value.  These flags are set up as follows:
#
# UR/UW: Owner read/write
# MR/MW: Member read/write
# OR: Other user read
# GR: Guest read
#
# NOTE: These flags are subject to change through iterations.
#
# Defaults for Courses:
# Default permissions for most courses should be UR,UW,MR,MW,
# allowing the owner and other instructor users read/write access
# to the course.  Syllabi in courses should be given permissions
# of UR,UW,MR,OR,GR so that guests and other users can read them,
# however the course does not need to be given guest read
# access as the syllabi will be shared as a special case.
#
# Defaults for Instructors:
# Instructors should be given UR,UW,MR,MW access in courses.
#
# Defauls for Students:
# Students should be given MR access.  This means that 
# students are not able to create or edit existing course content,
# but are able to view course content.  Grades are not considered
# to be course content, and these permissions do not pertain
# to students being able to take tests or participate in
# assignments and other related activities.

#-----------------------------------------------------------------------------
# Notes regarding System Roles
#
# The SystemRole is a simple integer as follows:
#
# 0:  A standard user.
# 1:  A sysop (Can view all courses and all course content)
# 2:  An admin (Can view and edit all courses and all course content)

import MySQLdb
import getpass
import getopt
import random
import time
import os
import sys

from iniparse import INIConfig
from hashlib import sha256

HELP_STRING = """
Usage: %s [OPTION]...

Create and initialize the CinciLMS database system.

-h, --host           The hostname of the MySQL server. Default "localhost".
-p, --port           The port onto which to connect.  Default 3306.
-d, --database       The name of the CinciLMS database. Default "CinciLMS".
-u, --username       The MySQL username used by CinciLMS. Default 'cincilms'.
-f, --force          Force recreation of the database. VERY DANGEROUS.
-P, --dbpasswd       Prompt for a database password when initializing.
                        Uses a randomly generated password by default.
-A, --adminpasswd    Prompt for an admin user password when initializing.
                        Uses a randomly generated password by default.
--help               Prints this help message.

Will prompt for confirmation if any data is to be destroyed.  Outputs a new
configuration file for CinciLMS to 'config.ini'.  Copy this to the site
directory if the database was (re)initialized.
""".strip ()

# ASCII upper case, lower case, and numeral characters used by
# the randomWord () function.
PASSWORD_CHARS = range (48, 58) + range (65, 91) + range (97, 123)

#-----------------------------------------------------------------------------
# MySQL Table Creation Statements
# These statements are listed in the order they should be run.

CREATE_TABLE_USERS = """
create table `Users` (
   `UserID` int not null auto_increment primary key,
   `ExternalID` int default null, INDEX (`ExternalID`),
   `Username` varchar (32), UNIQUE INDEX (`Username`),
   `FirstName` varchar (255),
   `MiddleInitial` char (1),
   `LastName` varchar (255),
   `EmailAddress` varchar (255),
   `PasswordSalt` char (64),
   `PasswordHash` char (64),
   `Notes` text default null,
   `LastLogin` date default null,
   `IsActive` tinyint (1) not null default 1,
   `SystemRole` int not null default 0
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_FILE_MANAGEMENT = """
create table `FileManagement` (
   `FileID` int not null auto_increment primary key,
   `OwnerID` int not null,
   `StorageFileName` varchar (255),
   `OriginalFileName` varchar (255)
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_CONTENT_TYPES = """
create table `ContentTypes` (
   `ContentTypeID` int not null primary key,
   `TypeName` varchar (255),
   `DefaultAccess` set ('UR','UW','MR','MW','OR','GR') not null default 'UR,UW,MR'
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_COURSE_CONTENT = """
create table `CourseContent` (
   `ContentID` int not null auto_increment primary key,
   `ParentID` int default null,
   `OwnerID` int not null,
   `TypeID` int not null,
   `Name` varchar (1023),
   `AccessFlags` set ('UR','UW','MR','MW','OR','GR') not null,
   constraint `FK_ContentParentID` foreign key (`ParentID`) references `CourseContent` (`ContentID`),
   constraint `FK_ContentOwnerID` foreign key (`OwnerID`) references `Users` (`UserID`) on delete cascade,
   constraint `FK_ContentTypeID` foreign key (`TypeID`) references `ContentTypes` (`ContentTypeID`)
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_CONTENT_LINKS = """
create table `ContentLinks` (
   `LinkID` int not null primary key,
   `DestinationID` int not null,
   constraint `FK_ContentLinkID` foreign key (`LinkID`) references `CourseContent` (`ContentID`),
   constraint `FK_LinkDestinationID` foreign key (`DestinationID`) references `CourseContent` (`ContentID`)
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_CONTENT_ITEMS = """
create table `ContentItems` (
   `ItemID` int not null primary key,
   `Title` varchar (1023) not null,
   `Text` text not null,
   constraint `FK_ContentItemID` foreign key (`ItemID`) references `CourseContent` (`ContentID`) on delete cascade
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_CONTENT_ITEM_ATTACHMENTS = """
create table `ContentItemAttachments` (
   `ContentID` int not null,
   `FileID` int not null,
   constraint `FK_AttachmentContentID` foreign key (`ContentID`) references `CourseContent` (`ContentID`),
   constraint `FK_AttachmentFileID` foreign key (`FileID`) references `FileManagement` (`FileID`),
   primary key (`ContentID`, `FileID`)
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_FACT_FOLDER_CONTENTS = """
create table `FactFolderContents` (
   `FolderID` int not null, INDEX (`FolderID`),
   `ContentID` int not null, INDEX (`ContentID`),
   primary key (`FolderID`, `ContentID`)
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_ASSIGNMENT_TYPES = """
create table `AssignmentTypes` (
   `AssignmentTypeID` int not null primary key,
   `TypeName` varchar (255)
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_ASSIGNMENTS = """
create table `Assignments` (
   `AssignmentID` int not null auto_increment primary key,
   `TypeID` int not null,
   `PointsPossible` int default 100,
   `DueDate` date default null,
   constraint `FK_AssignmentTypeID` foreign key (`TypeID`) references `AssignmentTypes` (`AssignmentTypeID`)
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_ASSIGNMENT_FILE_SUBMISSIONS = """
create table `AssignmentFileSubmissions` (
   `AssignmentID` int not null,
   `StudentID` int not null,
   `CourseID` int not null,
   `SubmissionDate` date not null,
   `FileID` int not null,
   constraint `FK_SubmissionAssignmentID` foreign key (`AssignmentID`) references `Assignments` (`AssignmentID`),
   constraint `FK_SubmissionStudentID` foreign key (`StudentID`) references `Users` (`UserID`),
   constraint `FK_SubmissionFileID` foreign key (`FileID`) references `FileManagement` (`FileID`),
   primary key (`AssignmentID`, `StudentID`, `CourseID`, `SubmissionDate`)
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_COURSES = """
create table `Courses` (
   `CourseID` int not null auto_increment primary key,
   `CourseName` varchar (255),
   `EntryPointID` int not null,
   `AccessFlags` set ('UR','UW','MR','MW','OR','GR') not null,
   constraint `FK_CourseEntryPointID` foreign key (`EntryPointID`) references `CourseContent` (`ContentID`)
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_COURSE_ROLES = """
create table `CourseRoles` (
   `RoleID` int not null primary key,
   `RoleName` varchar (255),
   `DefaultAccess` set ('CR','CW','GrR','GrW','EnR','EnW') not null
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_FACT_COURSE_ENROLLMENT = """
create table `FactCourseEnrollment` (
   `UserID` int not null, INDEX (`UserID`),
   `CourseID` int not null, INDEX (`CourseID`),
   `RoleID` int not null,
   `AccessFlags` set ('CR','CW','GrR','GrW','EnR','EnW')  not null,
   constraint `FK_EnrollmentUserID` foreign key (`UserID`) references `Users` (`UserID`),
   constraint `FK_EnrollmentCourseID` foreign key (`CourseID`) references `Courses` (`CourseID`),
   constraint `FK_EnrollmentRoleID` foreign key (`RoleID`) references `CourseRoles` (`RoleID`),
   primary key (`UserID`, `CourseID`)
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_GRADE_COLUMNS = """
create table `GradeColumns` (
   `ColumnID` int not null auto_increment primary key,
   `CourseID` int not null,
   `Name` varchar (255),
   `PointsPossible` int,
   `AssignmentID` int default null,
   `SortOrder` int default null,
   constraint `FK_ColumnCourseID` foreign key (`CourseID`) references `Courses` (`CourseID`),
   constraint `FK_ColumnAssignmentID` foreign key (`AssignmentID`) references `Assignments` (`AssignmentID`)
);
""".strip ()

#-----------------------------------------------------------------------------
CREATE_TABLE_GRADES = """
create table `Grades` (
   `ColumnID` int not null,
   `StudentID` int not null,
   `Grade` decimal (5,2),
   constraint `FK_GradeColumnID` foreign key (`ColumnID`) references `GradeColumns` (`ColumnID`),
   constraint `FK_GradeStudentID` foreign key (`StudentID`) references `Users` (`UserID`),
   primary key (`ColumnID`, `StudentID`)
);
""".strip ()

#-----------------------------------------------------------------------------
# Table descriptions and database enumerations.
#
TABLES_TO_CREATE = (
      ('Users',               CREATE_TABLE_USERS),
      ('File Management',     CREATE_TABLE_FILE_MANAGEMENT),
      ('Content Types',       CREATE_TABLE_CONTENT_TYPES),
      ('Course Content',      CREATE_TABLE_COURSE_CONTENT),
      ('Content Links',       CREATE_TABLE_CONTENT_LINKS),
      ('Content Items',       CREATE_TABLE_CONTENT_ITEMS),
      ('Content Attachments', CREATE_TABLE_CONTENT_ITEM_ATTACHMENTS),
      ('Folders',             CREATE_TABLE_FACT_FOLDER_CONTENTS),
      ('Assignment Types',    CREATE_TABLE_ASSIGNMENT_TYPES),
      ('Assignments',         CREATE_TABLE_ASSIGNMENTS),
      ('Assignment Files',    CREATE_TABLE_ASSIGNMENT_FILE_SUBMISSIONS),
      ('Courses',             CREATE_TABLE_COURSES),
      ('Course Roles',        CREATE_TABLE_COURSE_ROLES),
      ('Course Enrollments',  CREATE_TABLE_FACT_COURSE_ENROLLMENT),
      ('Grade Columns',       CREATE_TABLE_GRADE_COLUMNS),
      ('Grades',              CREATE_TABLE_GRADES))

INIT_CONTENT_TYPES = (
      ('1', 'Item'),
      ('2', 'Assignment'),
      ('3', 'Folder'),
      ('4', 'Link'))

INIT_ASSIGNMENT_TYPES = (
      ('1', 'Project Assignment'),)

INIT_COURSE_ROLES = (
      ('1', 'Student',        'CR,EnR'),
      ('2', 'Instructor',     'CR,CW,GrR,GrW,EnR,EnW'))

#-----------------------------------------------------------------------------
def randomWord (charspace = PASSWORD_CHARS, length = 6):
   """
      Creates a random password within the given ASCII character
      set with the specified number of digits.  Defaults to 6 digits.

      This is used by the initialization code below as confirmation
      in case an action would cause data to be destroyed.

      It is also used to generate random passwords for system and
      database accounts.
   """

   word = ''

   for i in xrange (length):
      word += chr (charspace [random.randint (0, len (charspace) - 1)])

   return word

#-----------------------------------------------------------------------------
def main (argv):
   """
      The main entry point.
   """
   
   shortopts = 'h:p:d:u:fPA'
   longopts = ['host=', 'port=', 'database=', 'user=', 'force', 'dbpasswd', 'adminpasswd']

   # If the user specified --help, print usage and exit.
   if '--help' in argv:
      usage ()

   hostname    = 'localhost'
   portNum     = 3306
   user        = 'cincilms'
   database    = 'CinciLMS'
   iniFile     = 'config.ini'
   rootPassword = None
   usePortNum  = False
   specifySQLPassword = False
   specifyAdminPassword = False

   # The INI file data.
   siteConfig = None

   newAdminPassword = None
   newSqlUserPassword = None

   db = None

   # If the INI file exists, read it.
   if os.path.exists (iniFile):
      siteConfig = INIConfig (open (iniFile, 'r'))
   else:
      siteConfig = INIConfig ()

   # Force recreation is very dangerous!
   forceRecreation = False

   opts, args = getopt.getopt (argv, shortopts, longopts)

   for opt, val in opts:
      if opt in ['-h', '--host']:
         hostname = str (val)
         usePortNum = True
      elif opt in ['-p', '--port']:
         portNum = int (val)
         usePortNum = True
      elif opt in ['-d', '--database']:
         database = str (val)
      elif opt in ['-u', '--user']:
         user = str (val)
      elif opt in ['-z', '--zipcsv']:
         zipCodeCSV = str (val)
      elif opt in ['-f', '--force']:
         # This is incredibly dangerous!  Don't do it!
         forceRecreation = True
      elif opt in ['-P', '--dbpasswd']:
         # Allow the user to specify a mysql password
         # for the database user.  This is convenient for
         # setting up mirrored sites that might use the same
         # database passwords, but usually only advised
         # for debugging purposes.
         specifySQLPassword = True
      elif opt in ['-A', '--adminpasswd']:
         # Specify what the initial admin password should be.
         specifyAdminPassword = True
   
   # Ask for the root password.
   print ('Please enter the root password for the MySQL server at %s:%d.' % (
         hostname, portNum))
   rootPassword = getpass.getpass ()

   # Attempt to connect to the MySQL server.
   print 'Attempting to connect to the MySQL server...'

   try:
      db = MySQLdb.connect (
            host = hostname,
            port = portNum,
            user = 'root',
            passwd = rootPassword)
   except Exception, excVal:
      print ('Failed to connect to the MySQL server.')
      print (str (excVal))
      print ('Aborting.')
      sys.exit (1)

   print ('Connection successful!')
   print ("")
   
   # Does the specified database exist?
   print ('Checking if database \'%s\' already exists...' % database)
   cursor = db.cursor ()
   cursor.execute ('select schema_name from information_schema.schemata where schema_name = %s',
         (database,))

   if cursor.fetchone ():
      print ('The database already exists!')
      print ('You can specify a different database with the \'--database\' option.')
      print ("")
      
      if forceRecreation:
         confirm = randomWord ()

         print ('WARNING!  You have chosen to force the recreation of the database.')
         print ('This will DESTROY the database and all data therein.  Are you sure?')
         print ('Only if you are sure, type %s.  Anything else cancels.' % confirm)
         print ("")
         sys.stdout.write ('ARE YOU SURE: ')
         answer = raw_input ()
         print ("")

         if answer != confirm:
            print ('You refused to destroy the database.')
            print ('Aborting.')
            sys.exit (1)
         else:
            print ('WAITING TEN SECONDS TO DESTROY THE DATABASE')
            print ('PRESS CTRL+C TO CANCEL')
            print ('THIS IS YOUR FINAL WARNING!')
            print
            time.sleep (10)

            cursor = db.cursor ()
            cursor.execute ('drop database %s' % database)
            print ('The database has been destroyed.')
      else:
         print ('Refused to destroy the database.')
         print ('Aborting.')
         print
         sys.exit (1)
   else:
      print ('The database does not exist.')
   print
   
   print ('Creating the database \'%s\'...' % database)
   cursor = db.cursor ()
   cursor.execute ('create database %s' % database)
   print ('The database has been created successfully.')
   print ("")

   cursor = db.cursor ()
   cursor.execute ('use %s' % database)
   
   for name, tableDef in TABLES_TO_CREATE:
      print ('Creating %s table...' % name)
      cursor.execute (tableDef)
   print ('All tables created successfully.')
   print ("")

   print ('Creating the database user and granting permissions...')
   cursor = db.cursor ()
   if specifySQLPassword:
      print ('Please enter a password for the database user \'%s\'.' % user)
      while newSqlUserPassword is None:
         passwdA = getpass.getpass ("SQL Password: ")
         passwdB = getpass.getpass ("Confirm Password: ")

         if passwdA == passwdB:
            newSqlUserPassword = passwdA
         else:
            print ('The passwords do not match.  Please try again.')
   else:
      newSqlUserPassword = randomWord (length = 16)

   cursor.execute ('grant all privileges on %s.* to \'%s\'@\'%s\' identified by \'%s\'' % (
      database, user, hostname, newSqlUserPassword))
   print ("")
   
   print ('Initializing the Content Types...')
   cursor = db.cursor ()
   cursor.executemany ('insert into `ContentTypes` (`ContentTypeID`, `TypeName`) values (%s, %s);',
         INIT_CONTENT_TYPES)
   print ("")

   print ('Initializing the Assignment Types...')
   cursor = db.cursor ()
   cursor.executemany ('insert into `AssignmentTypes` (`AssignmentTypeID`, `TypeName`) values (%s, %s);',
         INIT_ASSIGNMENT_TYPES)
   print ("")

   print ('Initializing the Course Roles...')
   cursor = db.cursor ()
   cursor.executemany ('insert into `CourseRoles` (`RoleID`, `RoleName`, `DefaultAccess`) values (%s, %s, %s);',
         INIT_COURSE_ROLES)
   print ("")

   print ('Generating the static salt...')
   staticSalt = sha256 (os.urandom (64)).hexdigest ()
   print ("")

   print ('Creating the \'admin\' user...')
   print ('Please provide a name for the Administrator.')
   sys.stdout.write ('First Name: ')
   adminFirstName = raw_input ()
   sys.stdout.write ('Last Name: ')
   adminLastName = raw_input ()

   if specifyAdminPassword:
      print ('Please enter a password for the admin user.')
      while newAdminPassword is None:
         passwdA = getpass.getpass ("Admin Password: ")
         passwdB = getpass.getpass ("Confirm Password: ")

         if passwdA == passwdB:
            newAdminPassword = passwdA
         else:
            print ('The passwords do not match.  Please try again.')
   else:
      newAdminPassword = randomWord (length = 8)

   newAdminUserSalt = sha256 (os.urandom (64)).hexdigest ()

   unsaltedPasswordHash = sha256 (staticSalt + newAdminPassword).hexdigest ()

   passwordHash = sha256 (newAdminUserSalt + unsaltedPasswordHash).hexdigest ()
   
   cursor = db.cursor ()
   cursor.execute ("insert into `Users` (`Username`, `FirstName`, `LastName`, \
      `SystemRole`, `PasswordSalt`, `PasswordHash`) values ('admin', %s, %s, 2, %s, %s)",
      (adminFirstName, adminLastName, newAdminUserSalt, passwordHash))
   adminID = cursor.lastrowid

   print ('LRS-DEBUG: Rows Inserted = %d' % cursor.rowcount)
   print ("")
   
   db.commit ()
   db.close ()

   if not specifyAdminPassword:
      print ("The admin user's password is: %s" % (newAdminPassword))

   siteConfig.db.hostname = hostname
   siteConfig.db.username = user
   siteConfig.db.password = newSqlUserPassword
   siteConfig.db.database = database
   siteConfig.db.static_salt = staticSalt
   
   # If the port or hostname weren't specified specifically, don't change them.
   # Recent versions of MySQLi will try to connect over TCP instead of a local
   # socket if the port num is specified at all.
   if usePortNum:
      siteConfig.db.port = portNum
    
   open (iniFile, 'w').write (str (siteConfig))

   print ('New settings written to \'%s\'.' % iniFile)
   print ('Please copy this file to the CinciLMS site directory.')
   print ('Goodbye!')
   print ("")
   sys.exit (0)


def usage ():
   """
      Prints usage and exits.
   """

   print HELP_STRING % sys.argv [0]
   sys.exit (0)

if __name__ == '__main__':
   main (sys.argv [1:])
      
      

