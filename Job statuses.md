# Job statuses

## Overview
This document provides information about what statuses created jobs have.

## Statuses

**Published** - the job is published on the site and is displayed to all users and guests of the site who can view the list of jobs. The published job is displayed for the author on the "Jobs Dashboard" page with the status "Not-filled".

Published job in wp-admin [WP-Admin > Job Board > Jobs]
![1  Published job in wp-admin](https://user-images.githubusercontent.com/28895658/227876480-c4c106b6-0e3a-4dde-a786-48111eba56ff.jpg)

Published job on the author's Dashboard page on the site
![1  Published job on the author's Dashboard page](https://user-images.githubusercontent.com/28895658/227877032-349d3467-e9a8-4f1b-9fe2-e020aacfc3a7.jpg)

**Draft** - this is a job that has not been published on the site and is not displayed in the list of jobs. Viewing a draft of the job is available to the author on the “Jobs Dashboard” page.

-	Administrator can create a draft job in wp-admin and on the site.
-	User can create a draft job on the site when creating a job on the Post Job page by clicking on the “Save Draft” button.
-	Guest, if the ability to publish jobs is available to him, can’t create a draft job.

Draft job in wp-admin [WP-Admin > Job Board > Jobs]
![2  Draft job in wp-admin](https://user-images.githubusercontent.com/28895658/227878041-6e6087fc-7315-45ac-897e-59510cb07ede.jpg)

Draft job on the author's Dashboard page on the site
![2  Draft job on the author's Dashboard page](https://user-images.githubusercontent.com/28895658/227878164-89d3105a-5495-487c-80a5-e72f83f2fa2e.jpg)

**Pending** - a job that has been created for publication but is pending approval by an administrator. Not displayed in the list of jobs for users and guests of the site. Viewing jobs in the pending approval status is available to the author on the “Jobs Dashboard” page.

In order for the job to receive the status pending upon publication and require verification and approval by the administrator, it is necessary that the “Set submissions as Pending” plugin setting is enabled.

Set submissions as Pending [WP-Admin > Job Board > Settings > General > Job Submission]
![Set submissions as Pending](https://user-images.githubusercontent.com/28895658/227878738-da534cd1-2c4f-4841-af01-b6eef5a0f28d.jpg)

Pending job in wp-admin [WP-Admin > Job Board > Jobs]
![3  Pending job in wp-admin](https://user-images.githubusercontent.com/28895658/227879094-c1d664d2-668b-4a3f-bf48-f09e87d3a7c6.jpg)

Pending job on the author's Dashboard page on the site
![3  Pending job on the author's Dashboard page](https://user-images.githubusercontent.com/28895658/227879332-99fb03cd-e096-4483-be82-14696316da41.jpg)

**Filled** - a job published on the site, which the author or administrator has marked as a filled vacancy. Displayed for the author, all users, and guests of the site who can view the list of jobs. For filled jobs, the opportunity to apply for a job is not available, and the “Apply for job” button on the individual job page is hidden.

Filled job in wp-admin [WP-Admin > Job Board > Jobs]
![4  Filled job in wp-admin](https://user-images.githubusercontent.com/28895658/227879591-22926d2e-fa1c-4e8d-a1ae-68ddd9ed72ae.jpg)

Filled job on the author's Dashboard page on the site
![4  Filled job on the author's Dashboard page](https://user-images.githubusercontent.com/28895658/227879737-c361dd19-973d-49da-99e3-9fbaa9e95de1.jpg)

Individual page - Filled job
![4  Individual page - Filled job](https://user-images.githubusercontent.com/28895658/227879915-e8a6c36a-07a4-41b3-9991-aa4a5113bff8.jpg)

**Expired** - a job published on the site for which the posting period has ended. Displayed for the author, all users, and guests of the site who can view the list of jobs. For Expired jobs, the opportunity to apply for a job is not available, the “Apply for job” button is hidden on the individual job page.

In order for the job to have a certain period of publication, it is necessary that the “Job duration” plugin setting field be filled in and the number of days of duration indicated, or if the “Show individual expiry date” setting is enabled, then the expiration date must be indicated in the “Expired date” field when publishing job.

Job duration [WP-Admin > Job Board > Settings > General > Job Submission]
![Job duration](https://user-images.githubusercontent.com/28895658/227880438-7da24f3e-d352-4f90-aa51-f891a0dc0d40.jpg)

Expired job in wp-admin [WP-Admin > Job Board > Jobs]
![5  Expired job in wp-admin](https://user-images.githubusercontent.com/28895658/227881589-5a16c2ca-2f72-46d6-bf98-078655d269af.jpg)

Expired job on the author's Dashboard page on the site
![5  Expired job on the author's Dashboard page](https://user-images.githubusercontent.com/28895658/227881739-784e0be0-994d-413b-9aeb-569a91a8aa80.jpg)

Individual page - Expired job
![5  Individual page - Expired job](https://user-images.githubusercontent.com/28895658/227881968-8a4fcfa7-9aa6-4f06-8bc4-2b161e927c5a.jpg)

`Note! Jobs with statuses Filled and Expired will not be displayed in the list of jobs if they are hidden for display in the plugin settings.`

WP-Admin > Job Board > Settings > General > Jobs List
![Jobs list](https://user-images.githubusercontent.com/28895658/227888684-774bf009-17a9-4165-b61b-693e954d783f.jpg)



