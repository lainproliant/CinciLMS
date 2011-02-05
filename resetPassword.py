#!/usr/bin/env python2

# Password Reset Script for the Cinci LMS system. 
# (c) 2011 Lee Supe
# Released under the GNU General Public License, version 3

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

Resets a user's password in the CinciLMS database system.

-u, --username:         The username who's password is to be reset.
-p, --specifyPassword   Prompt for a password.  Generates a random password
                           by default.
-c, --config            The path to the CinciLMS config file.
                           Default is config.ini.

If --specifyPassword was not given, a random password is generated for the
specified user and printed to standard out.  If --specifyPassword was
given, the user is prompted for a password and the password is not printed.

A valid config file is required to connect to the database, either in the 
current directory as 'config.ini' or at the path specified by --config.
""".strip ()

PASSWORD_CHARS = range (48, 58) + range (65, 91) + range (97, 123)

def randomWord (charspace = PASSWORD_CHARS, length = 6):
   """
      Creates a random password within the given ASCII character
      set with the specified number of digits.  Defaults to 6 digits.
   """

   word = ''

   for i in xrange (length):
      word += chr (charspace [random.randint (0, len (charspace) - 1)])

   return word

def main (argv):
   """
      The main entry point.
   """

   shortopts = 'pu:c:'
   longopts = ['specifyPassword', 'username=', 'config']

   # If user specified --help, print usage and exit.
   if '--help' in argv:
      usage ()

   iniFile = 'site/config.ini'
   username = None
   password = None
   port     = 3306
   specifyPassword = False
   randomPasswordLength = 6

   opts, args = getopt.getopt (argv, shortopts, longopts)

   for opt, val in opts:
      if opt in ['-p', '--specifyPassword']:
         specifyPassword = True
      elif opt in ['-u', '--username']:
         username = str (val)
      elif opt in ['-c', '--config']:
         iniFile = str (val)

   # The INI file data.
   siteConfig = INIConfig (open (iniFile, 'r'))

   if username is None:
      print ('No username specified for password reset.')
      print ('Aborting.')
      sys.exit (1)

   if siteConfig.db.port != '':
      port = int (siteConfig.db.port)

   # Attempt to connect to the MySQL server.
   print ('Attempting to connect to the MySQL server...')

   try:
      db = MySQLdb.connect (
            host = siteConfig.db.hostname,
            port = port,
            user = siteConfig.db.username,
            passwd = siteConfig.db.password,
            db = siteConfig.db.database)
   except Exception, excVal:
      print ('Failed to connect to the MySQL server.')
      print (str (excVal))
      print ('Aborting.')
      sys.exit (1)

   print ('Connection successful!')

   # Does the specified User exist?
   cursor = db.cursor ()
   cursor.execute ("Select 1 from Users where Username = %s", (username,))

   if cursor.rowcount < 1:
      print ('The specified User \'%s\' does not exist in the system.' % username)
      print ('Aborting.')
      sys.exit (1)

   # The user exists.  Should we create a random password or ask for one?
   if specifyPassword:
      while password is None:
         print ('Please specify a password.')
         passwordA = getpass.getpass ('New Password:')
         passwordB = getpass.getpass ('Confirm Password:')

         if passwordA != passwordB:
            print ('The passwords do not match.  Please try again.')
         else:
            password = passwordA
   else:
      password = randomWord (PASSWORD_CHARS, randomPasswordLength)
   
   # Set the new password.
   # Retrieve the user salt.
   cursor.execute ('Select PasswordSalt from Users where Username = %s', (username,))
   userSalt = cursor.fetchone ()[0]
   
   # Compute a password salted with the static salt and the user salt.
   saltedPassword = sha256 (
         userSalt + sha256 (siteConfig.db.static_salt + password).hexdigest ()).hexdigest ()

   cursor.execute ('Update Users set PasswordHash = %s where Username = %s', (
      saltedPassword, username))
   
   if not specifyPassword:
      print ('The User\'s new password is: %s' % password)
   else:
      print ('The User\'s password has been reset.')

   print ('Goodbye!')

   sys.exit (0)

def usage ():
   """
      Prints usage and exits.
   """

   print HELP_STRING % sys.argv [0]
   sys.exit (0)

if __name__ == '__main__':
   main (sys.argv[1:])

