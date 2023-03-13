# Checklist - Lang email flow (JobBoardWP with Polylang, WPML)

This document provides information on how email notifications are sent from JobBoardWP with Polylang, WPML plugin integration.

## "Job submitted" email

1. Admin **(Site Default lang)** = There is a translation of email for the language **"N"** in which the job is published => Admin receives an email in **"N"** language (body + subject)

2. Admin **(Site Default lang)** = There is NO translation of email for the language **"N"** in which the job is published=> Admin receives an email in **"Site Default lang"** (body + subject)

3. Admin (Selected **"K"** lang) = There is a translation of email for language **"K"** / There is a translation of email for the language **"N"** in which the job is published => Admin receives an email in **"K"** language (body + subject)

4. Admin (Selected **"P"** lang) = NO email translation for language **"P"** / There is a translation of email for the language **"N"** in which the job is published => Admin receives an email in **"Site Default lang"** (body + subject)

## "Job listing approved" email

1. User **(Site Default lang)** = There is a translation of email for the language **"N"**, in which the job was created, and approved by the admin => The user receives an email in **"N"** language (body + subject)

2. User **(Site Default lang)** = There is NO email translation for the language **"N"** in which the job was created, approved by the admin => The user receives an email in **"Site Default lang"** (body + subject)

3. User (Selected **"K"** lang) = There is a translation of email for language **"K"** / There is a translation of email for the language **"N"**, in which the job was created, and approved by the admin => The user receives an email in **"K"** language (body + subject)

4. User (Selected **"P"** lang) = NO email translation for language **"P"** / There is a translation of email for the language **"N"**, in which the job was created, and approved by the admin => The user receives an email in **"Site Default lang"** (body + subject)

## "Job has been edited" email

1. Admin **(Site Default lang)** = There is a translation of email for the language **"N"** in which the job is being edited => Admin receives an email in **"N"** language (body + subject)

2. Admin **(Site Default lang)** = There is NO email translation for the language **"N"** in which the job is being edited => Admin receives an email in **"Site Default lang"** (body + subject)

3. Admin (Selected **"K"** lang) = There is a translation of email for language **"K"** / There is a translation of email for the language **"N"** in which the job is being edited => Admin receives an email in **"K"** language (body + subject)

4. Admin (Selected **"P"** lang) = NO email translation for language **"P"** / There is a translation of email for the language **"N"** in which the job is being edited => Admin receives an email in **"Site Default lang"** (body + subject)

## "Job expiration reminder" email

1. User **(Site Default lang)** = There is a translation of the letter for the language **"N"**, in which the job was created, the publication period is expiring => The user receives an email in **"N"** language (body + subject)

2. User **(Site Default lang)** = There is no translation of the letter for the language **"N"** in which the job was created, the publication period is expiring => The user receives an email in **"Site Default lang"** (body + subject)

3. User (Selected **"K"** lang) = There is a translation of email for language **"K"** / There is a translation of email for the language **"N"**, in which the job was created, the publication period is expiring => The user receives an email in **"K"** language (body + subject)

4. User (Selected **"P"** lang) = No email translation for language **"P"** / There is a translation of email for the language **"N"**, in which the job was created, the publication period is expiring => The user receives an email in **"Site Default lang"** (body + subject)

