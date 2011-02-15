<?php

/*
 * Course: A class representation of a course in the system.
 * 
 * (c) 2011 Lee Supe
 * Released under the GNU General Public License, version 3.
 */

include_once "VO/CoursesVO.php";

define ("COURSE_ROLE_STUDENT", 1);
define ("COURSE_ROLE_INSTRUCTOR", 2);

function enumerateCoursePermissions ()
{
   return array (
      'UR'    => 'Owner Read',
      'UW'    => 'Owner Write',
      'MR'    => 'Member Read',
      'MW'    => 'Member Write',
      'OR'    => 'Others Read',
      'GR'    => 'Guests Read');
}

class Course extends CoursesVO {


}

