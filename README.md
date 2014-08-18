FAQ pages
================
This is an add-on to the FAQ module that allow the site admins to 
create custom FAQ pages from taxonomy terms.

The settings can be reached at /admin/config/content/faq/faq-pages.
There are the existing FAQ pages, and here can we add new ones or edit
the existing ones.

The structure of a custom FAQ page is the following:
 - title
 - path
 - description
 - blocks
   - block title
   - topics
     - topic title
     - topic description
     - terms

Note, that for this behaviour you have to add a taxonomy term reference
to your FAQ content type.

For development notes please see the [wiki page](https://github.com/hurtonypeter/drupal-faq_pages/wiki).
