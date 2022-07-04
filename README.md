# JobBoardWP with Polylang, WPML

This document contains information on how the JobBoardWP plugin is used with Polylang, WPML in the provided flows and what is the expected result for each use case.

### Contents

- [Get the "Job submitted" email if there is an email translation for the selected language of the job, using Polylang](#get-the-job-submitted-email-if-there-is-an-email-translation-for-the-selected-language-of-the-job-using-polylang)
 - [Get the "Job submitted" email when there is no email translation for the selected job language, using Polylang](#get-the-job-submitted-email-when-there-is-no-email-translation-for-the-selected-job-language-using-polylang)
 - [Get the "Job submitted" email if there is an email translation for the language set for the admin, using Polylang](#get-the-job-submitted-email-if-there-is-an-email-translation-for-the-language-set-for-the-admin-using-polylang)
 - [Get the "Job submitted" email when there is no email translation for the language set for the admin, using Polylang](#get-the-job-submitted-email-when-there-is-no-email-translation-for-the-language-set-for-the-admin-using-polylang)
 - [Get the "Job listing approved" email if there is an email translation for the selected language of the job, using Polylang](#get-the-job-listing-approved-email-if-there-is-an-email-translation-for-the-selected-language-of-the-job-using-polylang)
 - [Get the "Job listing approved" email when there is no email translation for the selected job language, using Polylang](#get-the-job-listing-approved-email-when-there-is-no-email-translation-for-the-selected-job-language-using-polylang)
 - [Get the "Job listing approved" email if there is an email translation for the user's language, using Polylang](#get-the-job-listing-approved-email-if-there-is-an-email-translation-for-the-users-language-using-polylang)
 - [Get the "Job listing approved" email when there is no email translation for the user's language, using Polylang](#get-the-job-listing-approved-email-when-there-is-no-email-translation-for-the-users-language-using-polylang)
 - [Get the "Job has been edited" email if there is an email translation for the selected language of the job, using Polylang](#get-the-job-has-been-edited-email-if-there-is-an-email-translation-for-the-selected-language-of-the-job-using-polylang)
 - [Get the "Job has been edited" email when there is no email translation for the selected job language, using Polylang](#get-the-job-has-been-edited-email-when-there-is-no-email-translation-for-the-selected-job-language-using-polylang)


## Get the "Job submitted" email if there is an email translation for the selected language of the job, using Polylang.

Pre-conditions to reproduce the test case:

1. **JobBoardWP** plugin must be activated
2. **Polylang** plugin must be activated
3. The necessary translation languages must be installed for the Polylang plugin.
4. The JobBoardWP email template "Job submitted" must have a translation created for the required language.

Steps to reproduce the test case:

1. As admin go to WP-Admin > In the upper right corner, hover over the name of the administrator - click on the link "Edit Profile"
2. On the "Profile" page, scroll down to the "Language" setting - check that the admin has the "Site Default" language set in the "Language" setting
3. Go to Job Board > Settings > Email - check that on the "JobBoardWP - Settings - Email" page for the "Job submitted" template, a translation of the required language has been created.
4. Go to the site as a logged in user > Switch page to the desired language > Post Job
5. On the "Post Job" page, fill in all the required fields > Click "Preview" button > Click "Submit Job" button
6. As admin go to WP-Admin > Job Board > Jobs - check that on the page "Jobs" in the list of jobs the created job is displayed and the language in which it was created is displayed in the column of installed languages.
7. Go to email log/admin mailbox - check that the "Job submitted" letter was sent/received to the admin at the email specified in the settings in the language in which the job was created.

Expected result:

 - After clicking on the "Edit Profile" link, the "Profile" page opens with profile settings (Users > All users > Admin profile page).
 - The "JobBoardWP - Settings - Email" page for the "Job submitted" template displays the created translation for the required language.
 - After switching the language on the site, the translation of the page into the selected language is displayed.
 - After clicking on the "Preview" button, the "Preview" page opens.
 - After clicking on the "Submit Job" button, the job has been successfully published and a notice example is displayed:
 >"Job is posted successfully. To view your job click here"
 - On the "Jobs" page in wp-admin, in the list of created jobs, the published job is displayed and in the column of installed languages, the language in which the job was created is displayed.
 - The email log shows the message sent to the address specified in the "Job submitted" template, the letter, and the subject of the letter with translation into the language in which the job was created.
 - If the admin has the "Site Default" language set, then he receives a letter "Job submitted" in the language in which the job was created if there is a translation of the email template for this language.

[Screencast Get the "Job submitted" email if there is an email translation for the selected language of the job, using Polylang](https://www.dropbox.com/s/839iwqvukz9gpva/001.%20Get%20the%20Job%20submitted%20email%20if%20there%20is%20an%20email%20translation%20for%20the%20selected%20language%20of%20the%20job%2C%20using%20Polylang.webm?dl=0)


## Get the "Job submitted" email when there is no email translation for the selected job language, using Polylang.

Pre-conditions to reproduce the test case:

1. **JobBoardWP** plugin must be activated
2. **Polylang** plugin must be activated
3. The necessary translation languages must be installed for the Polylang plugin.

Steps to reproduce the test case:

1. As admin go to WP-Admin > In the upper right corner, hover over the name of the administrator - click on the link "Edit Profile"
2. On the "Profile" page, scroll down to the "Language" setting - check that the admin has the "Site Default" language set in the "Language" setting
3. Go to Job Board > Settings > Email - check that on the "JobBoardWP - Settings - Email" page for the "Job submitted" template there is no translation into the required language.
4. Go to the site as a logged in user > Switch the page to the selected language for which there is no translation of the email template "Job submitted" > Post Job
5. On the "Post Job" page, fill in all the required fields > Click "Preview" button > Click "Submit Job" button
6. As admin go to WP-Admin > Job Board > Jobs - check that on the page "Jobs" in the list of jobs the created job is displayed and the language in which it was created is displayed in the column of installed languages.
7. Go to email log/admin mailbox - check that the "Job submitted" letter was sent/received to the admin at the email specified in the settings in the site's default language.

Expected result:

 - After clicking on the "Edit Profile" link, the "Profile" page opens with profile settings (Users > All users > Admin profile page).
 - The "JobBoardWP - Settings - Email" page for the "Job submitted" template does not have a created translation for the required language.
 - After switching the language on the site, the translation of the page into the selected language is displayed.
 - After clicking on the "Preview" button, the "Preview" page opens.
 - After clicking on the "Submit Job" button, the job has been successfully published and a notice example is displayed:
 >"Job is posted successfully. To view your job click here"
 - On the "Jobs" page in wp-admin, in the list of created jobs, the published job is displayed and in the column of installed languages, the language in which the job was created is displayed.
 - The email log shows the email sent to the address specified in the "Job submitted" template, the letter, and the subject of the letter in the site's default language.
 - If the admin has the "Site Default" language set, then he receives the letter "Job submitted" in the default language of the site since there is no translation of the email template for the language in which the job was created.

[Screencast Get the "Job submitted" email when there is no email translation for the selected job language, using Polylang](https://www.dropbox.com/s/q8nm9prxk3rzm7e/002.%20Get%20the%20Job%20submitted%20email%20when%20there%20is%20no%20email%20translation%20for%20the%20selected%20job%20language%2C%20using%20Polylang.webm?dl=0)


## Get the "Job submitted" email if there is an email translation for the language set for the admin, using Polylang.

Pre-conditions to reproduce the test case:

1. **JobBoardWP** plugin must be activated
2. **Polylang** plugin must be activated
3. The necessary translation languages must be installed for the Polylang plugin.
4. The JobBoardWP email template "Job submitted" must have a translation created for the required languages.

Steps to reproduce the test case:

1. As admin go to WP-Admin > In the upper right corner, hover over the name of the administrator - click on the link "Edit Profile"
2. On the "Profile" page, scroll down to the "Language" setting - in the drop-down list of languages, select the language for the administrator > Click "Update Profile" button.
3. Go to Job Board > Settings > Email - check that on the page "JobBoardWP - Settings - Email" for the template "Job submitted" a translation of the language that is set for the admin and the translation of other languages has been created.
4. Go to the site as a logged in user > Switch the page to the language for which the translation of the "Job submitted" email template was created and which is not set for the admin > Post Job
5. On the "Post Job" page, fill in all the required fields > Click "Preview" button > Click "Submit Job" button
6. As admin go to WP-Admin > Job Board > Jobs - check that on the page "Jobs" in the list of jobs the created job is displayed and the language in which it was created is displayed in the column of installed languages.
7. Go to email log/admin mailbox - check that the "Job submitted" letter was sent/received to the admin at the email specified in the settings in the language that is set for the admin profile.

Expected result:

 - After clicking on the "Edit Profile" link, the "Profile" page opens with profile settings (Users > All users > Admin profile page).
 - After clicking on the "Update Profile" button at the top of the "Profile" page, a notice is displayed in the installed language, for example:
 >"Profile updated."
 - On the "JobBoardWP - Settings - Email" page for the "Job submitted" template, the created translation for the language set for the admin profile, as well as for the language in which the vacancy is created, is displayed.
 - After switching the language on the site, the translation of the page into the selected language is displayed.
 - After clicking on the "Preview" button, the "Preview" page opens.
 - After clicking on the "Submit Job" button, the job has been successfully published and notice is displayed in the set page language, example:
 >"Job is posted successfully. To view your job click here"
 - On the "Jobs" page in wp-admin, in the list of created jobs, the published job is displayed and in the column of installed languages, the language in which the job was created is displayed.
 - The email log shows the message sent to the address specified in the "Job submitted" template, the letter, and the subject of the letter with translation into the language that is set for the admin profile.
 - If the admin has the selected profile language set, which is not the default language of the site, then he receives the letter "Job submitted" in this installed language, since there is a translation of the email template for this language.

[Screencast Get the "Job submitted" email if there is an email translation for the language set for the admin, using Polylang](https://www.dropbox.com/s/3y0tqog7a8ruecr/003.%20Get%20the%20Job%20submitted%20email%20if%20there%20is%20an%20email%20translation%20for%20the%20language%20set%20for%20the%20admin%2C%20using%20Polylang.webm?dl=0)


## Get the "Job submitted" email when there is no email translation for the language set for the admin, using Polylang.

Pre-conditions to reproduce the test case:

1. **JobBoardWP** plugin must be activated
2. **Polylang** plugin must be activated
3. The required translation language must be installed for the Polylang plugin.
4. The JobBoardWP email template "Job submitted" must have a translation created for the required language.

Steps to reproduce the test case:

1. As admin go to WP-Admin > In the upper right corner, hover over the name of the administrator - click on the link "Edit Profile"
2. On the "Profile" page, scroll down to the "Language" setting - in the drop-down list of languages, select the language for the administrator > Click "Update Profile" button.
3. Go to Job Board > Settings > Email - check that on the page "JobBoardWP - Settings - Email" for the template "Job submitted" there is no translation of the language set for the admin profile and there is a translation for the language in which the job is created.
4. Go to the site as a logged in user > Switch the page to the language for which the translation of the "Job submitted" email template was created > Post Job
5. On the "Post Job" page, fill in all the required fields > Click "Preview" button > Click "Submit Job" button
6. As admin go to WP-Admin > Job Board > Jobs - check that on the page "Jobs" in the list of jobs the created job is displayed and the language in which it was created is displayed in the column of installed languages.
7. Go to email log/admin mailbox - check that the "Job submitted" letter was sent/received to the admin at the email specified in the settings in the site's default language.

Expected result:

 - After clicking on the "Edit Profile" link, the "Profile" page opens with profile settings (Users > All users > Admin profile page).
 - After clicking on the "Update Profile" button at the top of the "Profile" page, a notice is displayed in the installed language, for example:
 >"Profile updated."
 - The "JobBoardWP - Settings - Email" page for the "Job submitted" template shows the created translation for the language in which the job is created and no translation for the language set for the admin profile.
 - After switching the language on the site, the translation of the page into the selected language is displayed.
 - After clicking on the "Preview" button, the "Preview" page opens.
 - After clicking on the "Submit Job" button, the job has been successfully published and notice is displayed in the set page language, for example:
 >"Job is posted successfully. To view your job click here"
 - On the "Jobs" page in wp-admin, in the list of created jobs, the published job is displayed and in the column of installed languages, the language in which the job was created is displayed.
 - The email log shows the email sent to the address specified in the "Job submitted" template, the letter, and the subject of the letter in the site's default language.
 - If the admin has a profile language set for which a translation of the "Job submitted" email template has not been created, then he receives a letter "Job submitted" in the site's default language.

[Screencast Get the "Job submitted" email when there is no email translation for the language set for the admin, using Polylang](https://www.dropbox.com/s/wqjr3mzufbmi2s9/004.%20Get%20the%20Job%20submitted%20email%20when%20there%20is%20no%20email%20translation%20for%20the%20language%20set%20for%20the%20admin%2C%20using%20Polylang.webm?dl=0)


## Get the "Job listing approved" email if there is an email translation for the selected language of the job, using Polylang.

Pre-conditions to reproduce the test case:

1. **JobBoardWP** plugin must be activated
2. **Polylang** plugin must be activated
3. The required translation language must be installed for the Polylang plugin.
4. The JobBoardWP email template "Job listing approved" must have a translation for the required language.
5. For JobBoardWP, the "Set submissions as Pending" setting checkbox must be checked (WP-admin > Settings > General > Job Submission)

Steps to reproduce the test case:

1. As admin go to WP-Admin > Users > All Users - on the "Users" page, hover over the selected user and click on the "Edit" link under the username
2. On the "Edit User" page, scroll down to the "Language" setting - check that the user has the "Site Default" language in the "Language" setting
3. Go to Job Board > Settings > Email - check that on the "JobBoardWP - Settings - Email" page for the "Job listing approved" template, a translation of the required language has been created.
4. Go to the site as a logged in user > Switch page to desired language > Post Job
5. On the "Post Job" page, fill in all the required fields > Click "Preview" button > Click "Submit Job" button
6. As admin go to WP-Admin > Job Board > Jobs - check that on the "Jobs" page in the list of jobs the created job with the status "Pending" is displayed and the language in which it was created is displayed in the column of installed languages.
7. On the "Jobs" page, hover over the created job > Click on the "Approve" link under the job
8. Go to the email log/mailbox of the user who created the job - check that a "Job listing approved" letter has been sent/received to the user's email specified in the profile in the language in which the job was created.

Expected result:

 - After clicking on the "Edit" link, the "Edit User" page opens with profile settings.
 - On the "JobBoardWP - Settings - Email" page for the "Job listing approved" template, the created translation for the required language is displayed.
 - After switching the language on the site, the translation of the page into the selected language is displayed.
 - After clicking on the "Preview" button, the "Preview" page opens.
 - After clicking on the "Submit Job" button, the job is submitted and notice is displayed, for example:
 >"Thank you for submitting your job. It will be appear on the website once approved."
 - On the "Jobs" page in wp-admin, in the list of created jobs, the created job with the status "Pending" is displayed and in the column of installed languages, the language in which the job was created is displayed.
 - After clicking on the "Approve" link, the job status changes from "Pending" to "Published" and a notice is displayed at the top of the "Jobs" page:
 >"1 job is approved."
 - The email log displays the letter sent to the user's email address specified in the profile, the letter and the subject of the letter with translation into the language in which the vacancy was created.
 - If the user has the "Site Default" language set, then he receives a letter "Job listing approved" in the language in which the vacancy was created, since there is a translation of the email template for this language.

[Screencast Get the "Job listing approved" email if there is an email translation for the selected language of the job, using Polylang](https://www.dropbox.com/s/31e0z3mbcy45ros/005.%20Get%20the%20Job%20listing%20approved%20email%20if%20there%20is%20an%20email%20translation%20for%20the%20selected%20language%20of%20the%20job%2C%20using%20Polylang.webm?dl=0)


## Get the "Job listing approved" email when there is no email translation for the selected job language, using Polylang.

Pre-conditions to reproduce the test case:

1. **JobBoardWP** plugin must be activated
2. **Polylang** plugin must be activated
3. The necessary translation languages must be installed for the Polylang plugin.
4. For JobBoardWP, the "Set submissions as Pending" setting checkbox must be checked (WP-admin > Settings > General > Job Submission)

Steps to reproduce the test case:

1. As admin go to WP-Admin > Users > All Users - on the "Users" page, hover over the selected user and click on the "Edit" link under the username
2. On the "Edit User" page, scroll down to the "Language" setting - check that the user has the "Site Default" language in the "Language" setting
3. Go to Job Board > Settings > Email - check that on the page "JobBoardWP - Settings - Email" for the template "Job listing approved" there is no translation into the required language.
4. Go to the site as a logged in user > Switch the page to the selected language for which there is no translation of the email template "Job submitted" > Post Job
5. On the "Post Job" page, fill in all the required fields > Click "Preview" button > Click "Submit Job" button
6. As admin go to WP-Admin > Job Board > Jobs - check that on the "Jobs" page in the list of jobs the created job with the status "Pending" is displayed and the language in which it was created is displayed in the column of installed languages.
7. On the "Jobs" page, hover over the created job > Click on the "Approve" link under the job
8. Go to the email log/mailbox of the user who created the job - check that a "Job listing approved" email has been sent to the user's email specified in the profile in the site's default language.

Expected result:

 - After clicking on the "Edit" link, the "Edit User" page opens with profile settings.
 - On the "JobBoardWP - Settings - Email" page for the "Job listing approved" template, there is no created translation for the required language.
 - After clicking on the "Preview" button, the "Preview" page opens.
 - After clicking on the "Submit Job" button, the job is submitted and notice is displayed, for example:
 >"Thank you for submitting your job. It will be appear on the website once approved."
 - On the "Jobs" page in wp-admin, in the list of created jobs, the created job with the status "Pending" is displayed and in the column of installed languages, the language in which the job was created is displayed.
 - After clicking on the "Approve" link, the job status changes from "Pending" to "Published" and a notice is displayed at the top of the "Jobs" page:
 >"1 job is approved."
 - The email log displays the message sent to the user's email address specified in the profile, the letter and the subject of the letter in the default language of the site.
 - If the user has the "Site Default" language set, then he receives a letter "Job listing approved" in the default language of the site, since there is no translation of the email template for the language in which the job was created.

[Screencast Get the "Job listing approved" email when there is no email translation for the selected job language, using Polylang](https://www.dropbox.com/s/2aohp4unv3tjmkg/006.%20Get%20the%20Job%20listing%20approved%20email%20when%20there%20is%20no%20email%20translation%20for%20the%20selected%20job%20language%2C%20using%20Polylang.webm?dl=0)


## Get the "Job listing approved" email if there is an email translation for the user's language, using Polylang.

Pre-conditions to reproduce the test case:

1. **JobBoardWP** plugin must be activated
2. **Polylang** plugin must be activated
3. The necessary translation languages must be installed for the Polylang plugin.
4. The JobBoardWP email template "Job listing approved" must have a translation created for the required languages.
5. For JobBoardWP, the "Set submissions as Pending" setting checkbox must be checked (WP-admin > Settings > General > Job Submission)

Steps to reproduce the test case:

1. As admin go to WP-Admin > Users > All Users - on the "Users" page, hover over the selected user and click on the "Edit" link under the username
2. On the "Edit User" page, scroll down to the "Language" setting - in the drop-down list of languages, select the language for the user > Click "Update Profile" button.
3. Go to Job Board > Settings > Email - check that on the page "JobBoardWP - Settings - Email" for the template "Job listing approved" a translation of the language set for the user has been created and a translation has been created for other languages.
4. Go to the site as a logged in user > Switch the page to the language for which the translation of the email template "Job listing approved" is created, but which is not set for the user > Post Job
5. On the "Post Job" page, fill in all the required fields > Click "Preview" button > Click "Submit Job" button
6. As admin go to WP-Admin > Job Board > Jobs - check that on the "Jobs" page in the list of jobs the created job with the status "Pending" is displayed and the language in which it was created is displayed in the column of installed languages.
7. On the "Jobs" page, hover over the created job > Click on the "Approve" link under the job
8. Go to the email log/mailbox of the user who created the job - check that a "Job listing approved" letter has been sent/received to the user's email specified in the profile in the language that is set for the user's profile.

Expected result:

 - After clicking on the "Edit" link, the "Edit User" page opens with profile settings.
 - After clicking on the "Update Profile" button at the top of the "Profile" page, notice is displayed in the specified language, for example:
 >"Profile updated."
 - On the "JobBoardWP - Settings - Email" page for the "Job listing approved" template, the created translation is displayed for the language set for the user profile, as well as for the language in which the job is created.
 - After switching the language on the site, the translation of the page into the selected language is displayed.
 - After clicking on the "Preview" button, the "Preview" page opens.
 - After clicking on the "Submit Job" button, the job is submitted and notice is displayed in the set page language, for example:
 >"Thank you for submitting your job. It will be appear on the website once approved."
 - On the "Jobs" page in wp-admin, in the list of created jobs, the created job with the status "Pending" is displayed and in the column of installed languages, the language in which the job was created is displayed.
 - After clicking on the "Approve" link, the job status changes from "Pending" to "Published" and a notice is displayed at the top of the "Jobs" page:
 >"1 job is approved."
 - The email log displays the message sent to the user's email address specified in the profile, the letter and the subject of the letter with translation into the language that is set for the user profile.
 - If the user has the selected profile language set, which is not the site's default language, then he receives the letter "Job listing approved" in this installed language, since there is a translation of the email template for this language.

[Screencast Get the "Job listing approved" email if there is an email translation for the user's language, using Polylang](https://www.dropbox.com/s/saezaoiv3bj0x0z/007.%20Get%20the%20Job%20listing%20approved%20email%20if%20there%20is%20an%20email%20translation%20for%20the%20user%27s%20language%2C%20using%20Polylang.webm?dl=0)


## Get the "Job listing approved" email when there is no email translation for the user's language, using Polylang.

Pre-conditions to reproduce the test case:

1. **JobBoardWP** plugin must be activated
2. **Polylang** plugin must be activated
3. The required translation language must be installed for the Polylang plugin.
4. For the JobBoardWP email template "Job listing approved", a translation must be created for the required language.
5. For JobBoardWP, the "Set submissions as Pending" setting checkbox must be checked (WP-admin > Settings > General > Job Submission)

Steps to reproduce the test case:

1. As admin go to WP-Admin > Users > All Users - on the "Users" page, hover over the selected user and click on the "Edit" link under the username
2. On the "Edit User" page, scroll down to the "Language" setting - in the drop-down list of languages, select the language for the user > Click "Update Profile" button.
3. Go to Job Board > Settings > Email - check that on the page "JobBoardWP - Settings - Email" for the template "Job listing approved" there is no translation of the language set for the user profile and there is a translation for the language in which the job is created.
4. Go to the site as a logged in user > Switch the page to the language for which the translation of the email template "Job listing approved" is created > Post Job
5. On the "Post Job" page, fill in all the required fields > Click "Preview" button > Click "Submit Job" button
6. As admin go to WP-Admin > Job Board > Jobs - check that on the "Jobs" page in the list of jobs the created job with the status "Pending" is displayed and the language in which it was created is displayed in the column of installed languages.
7. On the "Jobs" page, hover over the created job > Click on the "Approve" link under the job
8. Go to the email log/mailbox of the user who created the job - check that a "Job listing approved" letter has been sent/received to the user's email specified in the profile in the default language of the site.

Expected result:

 - After clicking on the "Edit" link, the "Edit User" page opens with profile settings.
 - After clicking on the "Update Profile" button at the top of the "Profile" page, a notice is displayed in the installed language, for example:
 >"Profile updated."
 - On the "JobBoardWP - Settings - Email" page for the "Job listing approved" template, it shows the created translation for the language in which the job is created and no translation for the language set for the user profile.
 - After switching the language on the site, the translation of the page into the selected language is displayed.
 - After clicking on the "Preview" button, the "Preview" page opens.
 - After clicking on the "Submit Job" button, the job has been successfully submitted and notice is displayed in the set page language, example:
 >"Thank you for submitting your job. It will be appear on the website once approved."
 - On the "Jobs" page in wp-admin, in the list of created jobs, the created job with the status "Pending" is displayed and in the column of installed languages, the language in which the job was created is displayed.
 - After clicking on the "Approve" link, the job status changes from "Pending" to "Published" and a notice is displayed at the top of the "Jobs" page:
 >"1 job is approved."
 - The email log displays the message sent to the user's email address specified in the profile, the letter and the subject of the letter in the default language of the site.
 - If the user has a profile language set for which the translation of the "Job listing approved" email template has not been created, then he receives a letter "Job listing approved" in the site's default language.

[Screencast Get the "Job listing approved" email when there is no email translation for the user's language, using Polylang](https://www.dropbox.com/s/50ri5nma5b5hp4h/008.%20Get%20the%20Job%20listing%20approved%20email%20when%20there%20is%20no%20email%20translation%20for%20the%20user%27s%20language%2C%20using%20Polylang.webm?dl=0)


## Get the "Job has been edited" email if there is an email translation for the selected language of the job, using Polylang.

Pre-conditions to reproduce the test case:

1. **JobBoardWP** plugin must be activated
2. **Polylang** plugin must be activated
3. The necessary translation languages must be installed for the Polylang plugin.
4. The JobBoardWP email template "Job has been edited" must have a translation created for the required language.
5. For JobBoardWP, the "Published Job Edits" setting must be set to "Users can edit their published job listing without approval by admin".
6. [Get the "Job submitted" email if there is an email translation for the selected language of the job, using Polylang](#get-the-job-submitted-email-if-there-is-an-email-translation-for-the-selected-language-of-the-job-using-polylang)

Steps to reproduce the test case:

1. As admin go to WP-Admin > In the upper right corner, hover over the name of the administrator - click on the link "Edit Profile"
2. On the "Profile" page, scroll down to the "Language" setting - check that the admin has the "Site Default" language set in the "Language" setting
3. Go to Job Board > Settings > Email - check that on the page "JobBoardWP - Settings - Email" for the template "Job has been edited" a translation of the required language in which the job was created has been created.
4. Go to the site as a logged in user > Switch the page to the selected language in which the job is created > Jobs Dashboard
5. On the "Jobs Dashboard" page, select a previously created job and click on the drop-down menu next to it > Edit
6. Edit job on the "Post Job" page > Click "Preview" button > Click "Submit Job" button
7. As admin go to email log/admin mailbox - check that the email "Job has been edited" was sent/received to the admin at the email specified in the settings in the language in which the job was created and edited.

Expected result:

 - After clicking on the "Edit Profile" link, the "Profile" page opens with the profile settings (Users > All users > Admin profile page).
 - The "JobBoardWP - Settings - Email" page for the "Job has been edited" template shows the created translation for the desired language.
 - After switching the language on the site, the translation of the page into the selected language is displayed.
 - After clicking on the "Edit" item of the drop-down list of the current job, the "Post Job" page opens.
 - After clicking on the "Preview" button, the "Preview" page opens.
 - After clicking on the "Submit Job" button, the job has been successfully published and notice is displayed, for example:
 >"Job is posted successfully. To view your job click here"
 - The email log displays the letter sent to the address specified in the "Job has been edited" template, the letter, and the subject of the letter with translation into the language in which the job was created and edited.
 - If the admin has the "Site Default" language set, then he receives a letter "Job has been edited" in the language in which the job was created and edited since there is a translation of the email template for this language.

[Screencast Get the "Job has been edited" email if there is an email translation for the selected language of the job, using Polylang](https://www.dropbox.com/s/bizze9zvl5dn4ry/009.%20Get%20the%20Job%20has%20been%20edited%20email%20if%20there%20is%20an%20email%20translation%20for%20the%20selected%20language%20of%20the%20job%2C%20using%20Polylang.mp4?dl=0)


## Get the "Job has been edited" email when there is no email translation for the selected job language, using Polylang.

Pre-conditions to reproduce the test case:

1. **JobBoardWP** plugin must be activated
2. **Polylang** plugin must be activated
3. The necessary translation languages must be installed for the Polylang plugin.
4. For JobBoardWP, the "Published Job Edits" setting must be set to "Users can edit their published job listing without approval by admin".
5. [Get the "Job submitted" email when there is no email translation for the selected job language, using Polylang](#get-the-job-submitted-email-when-there-is-no-email-translation-for-the-selected-job-language-using-polylang)

Steps to reproduce the test case:

1. As admin go to WP-Admin > In the upper right corner, hover over the name of the administrator - click on the link "Edit Profile"
2. On the "Profile" page, scroll down to the "Language" setting - check that the admin has the "Site Default" language set in the "Language" setting
3. Go to Job Board > Settings > Email - check that on the page "JobBoardWP - Settings - Email" for the template "Job has been edited" there is no translation in the required language in which the job was created.
4. Go to the site as a logged in user > Switch the page to the selected language in which the job was created and for which there is no translation of the email template "Job has been edited" > Jobs Dashboard
5. On the "Jobs Dashboard" page, select a previously created job and click on the drop-down menu next to it > Edit
6. Edit job on the "Post Job" page > Click "Preview" button > Click "Submit Job" button
7. As admin go to email log/admin mailbox - check that an email "Job has been edited" has been sent/received to the admin at the email specified in the settings in the site's default language.

Expected result:

 - After clicking on the "Edit Profile" link, the "Profile" page opens with the profile settings (Users > All users > Admin profile page).
 - The "JobBoardWP - Settings - Email" page for the "Job has been edited" template does not have a created translation for the required language.
 - After switching the language on the site, the translation of the page into the selected language is displayed.
 - After clicking on the "Edit" item of the drop-down list of the current job, the "Post Job" page opens.
 - After clicking on the "Preview" button, the "Preview" page opens.
 - After clicking on the "Submit Job" button, the job was successfully submitted and a notice example is displayed:
 >"Job is posted successfully. To view your job click here"
 - The email log shows the email sent to the address specified in the "Job has been edited" template, the letter and the subject of the letter in the site's default language.
 - If the admin has the "Site Default" language set, then he receives a letter "Job has been edited" in the default language of the site since there is no translation of the email template for the language in which the job was created and edited.

[Screencast Get the "Job has been edited" email when there is no email translation for the selected job language, using Polylang](https://www.dropbox.com/s/ts2viz8gwt4qi8g/010.%20Get%20the%20Job%20has%20been%20edited%20email%20when%20there%20is%20no%20email%20translation%20for%20the%20selected%20job%20language%2C%20using%20Polylang.webm?dl=0)
