TODO There is a lot of refactorin that can occur in the DBConnect file. There should be some blocks that can be combined into functions or some functions that can be given wrappers to have one of a few different ways of executing. Notifications for both queues should be optional and set on a per TA basis as an additional column on the table.
TODO Replace the area of code that is replacing lab with zoom in the students.js with a regex instead of lots of replaces (or the UI should show the title as set in the PHP instead of from the settings).
TODO Perhaps consider shrinking the FAQ link size
TODO Consider A combining a lot of the shared files into one, or using symlinks to be able to not have duplicate code.
TODO Try to remove as much queue page specific code as possible to essentially be able to just use one set of files.

HelpQueue
The help queue for CS 142.
The queue is used as a structured method for students to request and receive help.
It utilizes PHP and JS with a SQLite3 database datastore.

The queue was originally written for CS 240 but since it seemed effective, a lot of other CS classes have since copied it.

This version has been modified specifically for CS 142 by Nathan Shurtz in Fall 2018, but the modifications could be beneficial to the other classes.

The hope is that improvments can continue to be made and a record of the changes can be kept in this repository.

There are currently two major branches, master and Fall2018Modifications. What is in master is what is active on the helpe queue page. The changes in Fall2018Modifications are not implemented on the page, but theoretically should work just fine.
The master branch should only ever have changes that have been approved and tested merged/pushed/pulled. This keeps the changes organized and easy to revert back to something that worked without too much effort.
To update the actual site, make sure that the changes have been approved and tested, merge them into master, check out master, navigate in a file explorer to "/users/groups/cs142ta/HelpQueue" and select all of the files and folders (except the database file) and copy them to "/users/groups/cs142ta/public_html/inlab-helpqueue"
Updates to the site should be done at a time when students aren't actively using the queue. Early mornings or after the TAs are done working are the best time for this.
If there were changes to any of the Javascript files, it is likely that everyone will have to reload the page ignoring cached files. To do this, simply load the page and press ctrl-shift-r for Windows and Linux, or cmd-shift-r for Mac. This should work in any browser, if it does not, look up how to clear the cache for a specific website for your specific browser.
Changes to any of the other files shouldn't really need this, but it doesn't hurt.

There exists what is referred to as the "Test Queue". This is located at "/users/groups/cs142ta/public_html/inlab-helpqueueQA" and the page URL is "https://students.cs.byu.edu/~cs142ta/inlab-helpqueueQA".
The idea is that this is where you can test and validate the changes made in the other branches work and don't break anything. Ideally, what should happen is the changes (except the database) can be copied to "/users/groups/cs142ta/public_html/inlab-helpqueueQA" and the database should be set as a symlink to the database in "/users/groups/cs142ta/public_html/inlab-helpqueue" if it isn't already.
Using the sybolic link allows the individuals testing to see the real data rather than fake data that doesn't change.

There also exists another "Development Queue" which is used for being able to test out the changes before the go to the "Test Queue". This is located at "/users/groups/cs142ta/public_html/inlab-helpqueueDEV" and the page URL is "https://students.cs.byu.edu/~cs142ta/inlab-helpqueueDEV".
Here there should not be a symlink to the database in "/users/groups/cs142ta/public_html/inlab-helpqueue" as we do not what to mess with real data while developing. Copy the changes to the DEV directory and copy the actual database to the DEV directory as well. This will ensure that the database is set up and has some data in it.
For the sake of testing from both a TA and Student perspective, it may be required to recruit another TA or two to help you. You can deactivate their TA status so they can be "students".
It is also possible to make another BYU NetID and utilize that to test by yourself. It is also possible to disable CAS authentication, but I strongly advise against doing that.

The back end is done in PHP and the frontend is done in mostly Javascript.
The page for the queue is dynamically created using the PHP based on whether or no the user is a Student or a TA. This was added in the Fall2018Modifications branch to reduce the amount of Javscript the students download to use the queue as before they got all of the same Javascript as the TAs which could be a potential vulnerability.
After the page is generated and loaded the Javascript takes over and refreshes the information shown on a regular interval (this is able to be changed by any TA).
When a student gets in the queue, they are added to a list of people currently waiting to be helped. When a TA offers assistence, they are moved to a list of people currently being helped.
The Javascript utilizes Jquery to send additional requests to the server where the PHP functions take over. The majority of methods that are only accessible to TAs on the page should have authorization checks in place. This prevents any student with a lot of experience from being able to do thing such as remove another student from the queue. However, since the change to the dynamically generated page, this should be even less possible as they cannot see the Javascript the TAs use, but the server side authorization checks should still be done.
The majority of the code is in "DBConnect.php" and "index.js" or "index_student.js". "index_student.js" obviously contains the Javascript the students will get and "index.js" contains all of the Javascript, both for students and for TAs (it would be possible to take out the student only portions, but that hasn't been done yet).
The Javascript files that actually get served should be the .min (minified) versions, but that isn't 100% neccessary. The unminified Javascript files exist because they are significantly easier to read and modify.


The idea of 2 seperate lists for students is only in the code as it appears as one list on the page.
This change was made during Fall 2018 by Nathan Shurtz to prevent "double clicks" where one TA would click to help a student at the same time as another. The result is that the student was immediately removed from the queue as if they had finished being helped.
Another change made during this time was the addition of the setting to limit the number of times a student could ask questions per day or per week. It is possible to disable either by simply changing their limit to -1 in the settings. The students are able to see the number of questions they have remaining at the bottom of their page only if the limit is active. If the limit is changed while a student has the queue loaded they will see some negative numbers where the limit information normally is, this is due to the fact that the PHP has already generated the page. It would be possible to use Javascript to hide these elements dynamically, but it is unlikely that the setting be changed while a student is actively using the queue. It is also fixed if they simply refresh their page.

I have made a test user that you should be able to utilize for testing sake. The username is testin5 and the password is 5Eg4bjrh*T%K